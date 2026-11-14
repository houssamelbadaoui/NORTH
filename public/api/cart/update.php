<?php

require_once __DIR__ . "/../../../config/session.php"; // start session

header("Content-Type: application/json; charset=UTF-8");


// response helper to send to js
function sendResponse(bool $success, string $message, int $statusCode = 200, ?string $code = null, array $data = []): void
{
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
    sendResponse(false, "Please log in to update your cart.", 401, "AUTH_REQUIRED");
}

// Only customers can update carts
if (($_SESSION["role"] ?? "") !== "customer") {
    sendResponse(false, "You are not authorized to update the cart.", 403, "FORBIDDEN");
}

// read json request body 
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    sendResponse(false, "Invalid JSON request.", 400, "INVALID_REQUEST");
}

// retrieve product Id and quantity
$productId = $input["productId"] ?? "";
$change = $input["change"] ?? "";

// Validate product ID
if (
    !is_string($productId) ||
    !preg_match('/^[a-f0-9]{24}$/i', $productId)
) {
    sendResponse(false, "Invalid product ID.", 400, "INVALID_PRODUCT_ID");
}

// Only allow +1 or -1
if (!is_int($change) || !in_array($change, [1, -1], true)) {
    sendResponse(false, "Invalid quantity change.", 400, "INVALID_QUANTITY_CHANGE");
}


// connect to the database, use a transaction so we can update the stock and update product quantity
try{
    require_once __DIR__ . "/../../../config/database.php";

    $productObjectId = new MongoDB\BSON\ObjectId($productId);
    $userId = $_SESSION["user_id"]; // user´s id from the session

    // start mongodb transaction
    $session = $client->startSession();
    $session->startTransaction();

    try{
        // find the customer´s cart
        $customerCart = $cart->findOne(
            ["userId" => $userId],
            ["session" => $session]
        );

        if($customerCart === null){
            $session->abortTransaction();

            sendResponse(false, "Your cart is empty.", 404, "CART_NOT_FOUND");
        }

        // find the requested item inside the cart
        $currentQuantity = null;
        foreach($customerCart["items"] as $item){
            if((string) $item["productId"] === $productId){
                $currentQuantity = (int) $item["quantity"];
                break;
            }
        }

        if($currentQuantity === null){
            $session->abortTransaction();

            sendResponse(false, "Product not found in your cart.", 404, "ITEM_NOT_FOUND");
        }

        // prevent quantity from going under 1
        if($change === -1 && $currentQuantity <= 1){
            $session->abortTransaction();

            sendResponse(
                false,
                "Minimum quantity is 1. Use Remove to delete this product.",
                400,
                "MINIMUM_QUANTITY"
            );
        }

         // Check that the product exists
        $product = $products->findOne(
            ["_id" => $productObjectId],
            ["session" => $session]
        );

        if ($product === null) {
            $session->abortTransaction();

            sendResponse(false, "Product not found.", 404, "PRODUCT_NOT_FOUND");
        }

         // Current timestamp
        $now = new MongoDB\BSON\UTCDateTime(
            (int) (microtime(true) * 1000)
        );

        if($change === 1){
             // Reserve one additional unit.
            // Stock must be at least 1.
            $stockResult = $products->updateOne(
                [
                    "_id" => $productObjectId,
                    "stock" => ['$gte' => 1]
                ],
                [
                    '$inc' => ["stock" => -1]
                ],
                [
                    "session" => $session
                ]
            );
            if ($stockResult->getMatchedCount() === 0) {
                $session->abortTransaction();

                sendResponse(
                    false,
                    "Not enough stock available.",
                    409,
                    "INSUFFICIENT_STOCK"
                );
            }
        }else{
             // Return one reserved unit to available stock.
            $products->updateOne(
                ["_id" => $productObjectId],
                [
                    '$inc' => ["stock" => 1]
                ],
                [
                    "session" => $session
                ]
            );
        }

         // Update the quantity in the customer's cart
        $cartResult = $cart->updateOne(
            [
                "userId" => $userId,
                "items.productId" => $productObjectId
            ],
            [
                '$inc' => [
                    "items.$.quantity" => $change
                ],
                '$set' => [
                    "updatedAt" => $now
                ]
            ],
            [
                "session" => $session
            ]
        );

        if ($cartResult->getMatchedCount() === 0) {
            throw new RuntimeException("Cart item could not be updated.");
        }

        // Retrieve updated cart
        $updatedCart = $cart->findOne(
            ["userId" => $userId],
            ["session" => $session]
        );

        if ($updatedCart === null) {
            throw new RuntimeException("Updated cart could not be retrieved.");
        }

        // Calculate total number of units
        $cartCount = 0;

        foreach ($updatedCart["items"] as $item) {
            $cartCount += (int) $item["quantity"];
        }

        // Commit all changes
        $session->commitTransaction();

        sendResponse(
            true,
            "Cart quantity updated successfully.",
            200,
            null,
            ["cartCount" => $cartCount]
        );
    }catch(Throwable $e){
         // Roll back all changes
        try {
            $session->abortTransaction();
        } catch (Throwable $abortError) {
            error_log("UPDATE CART ROLLBACK ERROR: " . $abortError->getMessage());
        }

        throw $e;

    }
}catch(Throwable $error){
      error_log("UPDATE CART ERROR: " . $error->getMessage());

    sendResponse(
        false,
        "Something went wrong while updating your cart.",
        500,
        "SERVER_ERROR"
    );
}