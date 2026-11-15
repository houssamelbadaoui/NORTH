<?php

require_once __DIR__ . "/../../../config/session.php";

header("Content-Type: application/json; charset=UTF-8");

// JSON response helper
function sendResponse(
    bool $success,
    string $message,
    int $statusCode = 200,
    ?string $code = null,
    array $data = []
): void {
    http_response_code($statusCode);

    $response = [
        "success" => $success,
        "message" => $message
    ];

    if ($code !== null) {
        $response["code"] = $code;
    }

    if (!empty($data)) {
        $response["data"] = $data;
    }

    echo json_encode($response);
    exit;
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Invalid request method.", 405, "METHOD_NOT_ALLOWED");
}

// Check authentication
if (!isset($_SESSION["user_id"])) {
    sendResponse(false, "Please log in to place an order.", 401, "AUTH_REQUIRED");
}

// Only customers can place orders
if (($_SESSION["role"] ?? "") !== "customer") {
    sendResponse(false, "You are not authorized to place orders.", 403, "FORBIDDEN");
}

try{
    require_once __DIR__ . "/../../../config/database.php";

    $userId = $_SESSION["user_id"];

    // Start MongoDB transaction
    $session = $client->startSession();
    $session->startTransaction();

    try{
        // Find customer's cart
        $customerCart = $cart->findOne(
            ["userId" => $userId],
            ["session" => $session]
        );

          if ($customerCart === null || empty($customerCart["items"])) {
            $session->abortTransaction();

            sendResponse(
                false,
                "Your cart is empty.",
                400,
                "CART_EMPTY"
            );
        }

        // collect products 
        $productIds = [];

        foreach ($customerCart["items"] as $item) {
            $productIds[] = $item["productId"];
        }

        // Retrieve products from MongoDB
        $cursor = $products->find(
            ["_id" => ['$in' => $productIds]],
            ["session" => $session]
        );

        // Organize products by ID
        $productsById = [];

        foreach ($cursor as $product) {
            $productsById[(string) $product["_id"]] = $product;
        }

        // Build order items and calculate total
        $orderItems = [];
        $totalCents = 0;

        foreach($customerCart["items"] as $item){
            $productId = (string) $item["productId"];
            $quantity = (int) $item["quantity"];

            $product = $productsById[$productId];

            $unitPriceCents = (int) $product["priceCents"];
            $lineTotalCents = $unitPriceCents * $quantity;

            $totalCents += $lineTotalCents;

            // Save a snapshot of the product
            $orderItems[] = [
                "productId" => $item["productId"],
                "name" => $product["name"],
                "imageUrl" => $product["imageUrl"] ?? null,
                "unitPriceCents" => $unitPriceCents,
                "quantity" => $quantity,
                "lineTotalCents" => $lineTotalCents
            ];
        }

        // Generate order ID and order number
        $orderId = new MongoDB\BSON\ObjectId();

        // Current timestamp
        $now = new MongoDB\BSON\UTCDateTime(
            (int) (microtime(true) * 1000)
        );

        // Create the order
        $orders->insertOne(
            [
                "_id" => $orderId,
                "userId" => $userId,
                "items" => $orderItems,
                "totalCents" => $totalCents,
                "status" => "pending",
                "createdAt" => $now,
                "updatedAt" => $now
            ],
            ["session" => $session]
        );

        // Empty the customer's cart
        $cartResult = $cart->updateOne(
            ["userId" => $userId],
            [
                '$set' => [
                    "items" => [],
                    "updatedAt" => $now
                ]
            ],
            ["session" => $session]
        );

        if ($cartResult->getMatchedCount() === 0) {
            throw new RuntimeException("Customer cart could not be cleared.");
        }

        // Commit transaction
        $session->commitTransaction();

        sendResponse(
            true,
            "Your order has been placed successfully.",
            201,
            null
        );
    }catch(Throwable $e){
         try {
            $session->abortTransaction();
        } catch (Throwable $abortError) {
            error_log("ORDER ROLLBACK ERROR: " . $abortError->getMessage());
        }

        throw $e;
    }
}catch(Throwable $error){
    error_log("ADD ORDER ERROR: " . $error->getMessage());

    sendResponse(
        false,
        "Something went wrong while placing your order.",
        500,
        "SERVER_ERROR"
    );
}
