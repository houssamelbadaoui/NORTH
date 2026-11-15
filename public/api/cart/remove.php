<?php

require_once __DIR__ . "/../../../config/session.php";

header("Content-Type: application/json; charset=UTF-8");

// JSON response helper
function sendResponse(bool $success, string $message, int $statusCode = 200, ?string $code = null, array $data = []): void {
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
    sendResponse(false, "Please log in to remove products from your cart.", 401, "AUTH_REQUIRED");
}

// Only customers can modify carts
if (($_SESSION["role"] ?? "") !== "customer") {
    sendResponse(false, "You are not authorized to modify the cart.", 403, "FORBIDDEN");
}

// Read JSON request body
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    sendResponse(false, "Invalid JSON request.", 400, "INVALID_REQUEST");
}

// Retrieve product ID
$productId = $input["productId"] ?? "";

// Validate product ID
if ( !is_string($productId) || !preg_match('/^[a-f0-9]{24}$/i', $productId)) {
    sendResponse(false, "Invalid product ID.", 400, "INVALID_PRODUCT_ID");
}

try{
    require_once __DIR__ . "/../../../config/database.php";

    $productObjectId = new MongoDB\BSON\ObjectId($productId);
    $usersId = $_SESSION["user_id"];

    // start and mongodb transaction
    $session = $client->startSession();
    $session->startTransaction();

    try{
        // find customer´s cart
        $customerCart = $cart->findOne(
            ["userId" => $usersId],
            ["session" => $session]
        );

        // if no cart
        if($customerCart === null){
            $session->abortTransaction();

            sendResponse(false, "Your cart is empty.", 404, "CART_NOT_FOUND");
        }

        // find product quantity in the cart
        $reservedQuantity = null;

        foreach($customerCart["items"] as $item){
            if((string) $item["productId"] === $productId){
                $reservedQuantity = (int) $item["quantity"];
                break;
            }
        }

        if($reservedQuantity === null){
            $session->abortTransaction();

             sendResponse(
                false,
                "Product not found in your cart.",
                404,
                "ITEM_NOT_FOUND"
            );
        }

        // we find product 
        $product = $products->findOne(
            ["_id" => $productObjectId],
            ["session" => $session]
        );

          if ($product === null) {
            $session->abortTransaction();

            sendResponse(
                false,
                "Product not found. Its reserved stock could not be restored.",
                404,
                "PRODUCT_NOT_FOUND"
            );
        }

         // Current timestamp
        $now = new MongoDB\BSON\UTCDateTime(
            (int) (microtime(true) * 1000)
        );

         // Return the entire reserved quantity to available stock
        $stockResult = $products->updateOne(
            ["_id" => $productObjectId],
            [
                '$inc' => [
                    "stock" => $reservedQuantity
                ]
            ],
            ["session" => $session]
        );

         if ($stockResult->getMatchedCount() === 0) {
            throw new RuntimeException("Product stock could not be restored.");
        }

         // Remove the product from the customer's cart
        $cartResult = $cart->updateOne(
            [
                "userId" => $usersId,
                "items.productId" => $productObjectId
            ],
            [
                '$pull' => [
                    "items" => [
                        "productId" => $productObjectId
                    ]
                ],
                '$set' => [
                    "updatedAt" => $now
                ]
            ],
            ["session" => $session]
        );

         if ($cartResult->getMatchedCount() === 0) {
            throw new RuntimeException("Cart item could not be removed.");
        }

         // Retrieve the updated cart
        $updatedCart = $cart->findOne(
            ["userId" => $usersId],
            ["session" => $session]
        );

         if ($updatedCart === null) {
            throw new RuntimeException("Updated cart could not be retrieved.");
        }

        // Calculate the remaining number of units
        $cartCount = 0;

          foreach ($updatedCart["items"] as $item) {
            $cartCount += (int) $item["quantity"];
        }

           // Commit stock and cart changes
        $session->commitTransaction();

        sendResponse(
            true,
            "Product removed from your cart.",
            200,
            null,
            [
                "cartCount" => $cartCount
            ]
        );
    }catch(Throwable $e){

        // Roll back all changes if something fails
        try {
            $session->abortTransaction();
        } catch (Throwable $abortError) {
            error_log("REMOVE CART ROLLBACK ERROR: " . $abortError->getMessage());
        }

        throw $e;
    }
}catch(Throwable $error){
      error_log("REMOVE CART ERROR: " . $error->getMessage());

    sendResponse(
        false,
        "Something went wrong while removing the product.",
        500,
        "SERVER_ERROR"
    );
}