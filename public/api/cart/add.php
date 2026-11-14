<?php

require_once __DIR__ . "/../../../config/session.php";

header("Content-Type: application/json; charset=UTF-8");

// JSON response helper
function sendResponse(bool $success, string $message, int $statusCode = 200, ?string $code = null, array $data = []): void{
    http_response_code($statusCode);

    $response = [
        "success" => $success,
        "message" => $message
    ];


    if($code !== null){
        $response["code"] = $code; // if code not null we send code
    }

    if(!empty($data)){
        $response["data"] = $data; // if data not empty array we send data
    }

    echo json_encode($response);
    exit;
}

// only accept POST request
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    sendResponse(false, "Invalid request method", 405, "METHOD_NOT_ALLOWED");
}

// check the user is authenticathed
if(!isset($_SESSION["user_id"])){
    sendResponse(false, "Please log in to add Products to your cart.", 401, "AUTH_REQUIRED");
}

// only customers can add to cart
if($_SESSION["role"] !== "customer"){
    sendResponse(false, "You are not authorized to use the shopping cart.", 403, "FORBIDEN");
}

// read JSON request body 
$input = json_decode(file_get_contents("php://input"), true);

// validate 
if(!is_array($input)){
    sendResponse(false, "Invalid JSON request.", 400, "INVALID_REQUEST");
}

// retrieve product id and quantity
$productId = $input["productId"] ?? "";
$quantity = $input["quantity"] ?? null;


// validate 
if (
    !is_string($productId) ||
    !preg_match('/^[a-f0-9]{24}$/i', $productId)
) {
    sendResponse(
        false,
        "Invalid product ID.",
        400,
        "INVALID_PRODUCT_ID"
    );
}

// 7. Validate quantity
if (!is_int($quantity) || $quantity < 1) {
    sendResponse(
        false,
        "Invalid product quantity.",
        400,
        "INVALID_QUANTITY"
    );
}

// connect to mongodb and start a transaction

try{
    require_once __DIR__ . "/../../../config/database.php";

    // convert the product ID string into a Mongodb ObjectId
    $productObjectId = new MongoDB\BSON\ObjectId($productId);

    // customer ID from the PHP session
    $userId = $_SESSION["user_id"];

    // start a Mongodb session and transaction
    $session = $client->startSession();
    $session->startTransaction();

    try{

    // reserve stock automatically 
    $stockResult = $products->updateOne(
        [
            "_id" => $productObjectId,
            "active" => true,
            "stock" => ['$gte' => $quantity]
        ],
        [
            '$inc' => [
                "stock" => -$quantity
            ]
        ],
        [
            "session" => $session
        ]
    );

    // if stock update failed
    if($stockResult->getMatchedCount() === 0){
          // Find out why the stock update failed
            $product = $products->findOne(
                ["_id" => $productObjectId],
                ["session" => $session]
            );

            $session->abortTransaction();

              if ($product === null || $product["active"] !== true) {
                sendResponse(
                    false,
                    "This product is no longer available.",
                    404,
                    "PRODUCT_UNAVAILABLE"
                );
            }

            sendResponse(
                false,
                "Not enough stock available.",
                409,
                "INSUFFICIENT_STOCK"
            );
    }

    // find the customer cart
    $customerCart = $cart->findOne(
        ["userId" => $userId],
        ["session" => $session]
    );

     $now = new MongoDB\BSON\UTCDateTime(
            (int) (microtime(true) * 1000)
        );

    // create a cart if the customer doesnt have one 
    if($customerCart === null){
        $cart->insertOne(
            [
                "userId" => $userId,
                "items" => [
                    [
                        "productId" => $productObjectId,
                        "quantity" => $quantity
                    ]
                ],
                "createdAt" => $now,
                "updatedAt" => $now
            ],
            [
                "session" => $session
            ]
        );
    }else{
        // check inside the exisiting cart if product already exists
        $existingItem = $cart->findOne(
            [
                "userId" => $userId,
                "items.productId" => $productObjectId
            ],
            [
                "session" => $session
            ]
        );

        if($existingItem !== null){
            // increase the exisiting product quantity
            $cart->updateOne(
                [
                    "userId" => $userId,
                    "items.productId" => $productObjectId
                ],
                [
                    '$inc' => [
                            "items.$.quantity" => $quantity
                        ],
                        '$set' => [
                            "updatedAt" => $now
                        ]
                ],
                [
                    "session" => $session
                ]
            );
        }else{
              // Add a new product to the existing cart
                $cart->updateOne(
                    ["userId" => $userId],
                    [
                        '$push' => [
                            "items" => [
                                "productId" => $productObjectId,
                                "quantity" => $quantity
                            ]
                        ],
                        '$set' => [
                            "updatedAt" => $now
                        ]
                    ],
                    [
                        "session" => $session
                    ]
                );
        }
    }

    // retrieve the updated cart
    $updatedCart = $cart->findOne(
        ["userId" => $userId],
        ["session" => $session]
    );

    if($updatedCart === null){
        throw new RuntimeException("Cart could not be retrieved.");
    }

    // calculate number of units
    $cartCount = 0;

    foreach($updatedCart["items"] as $item){
        $cartCount += (int) $item["quantity"];
    }

    $session->commitTransaction();

    sendResponse(true, "Product added to cart.", 200, null, ["cartCount" => $cartCount]);
    }catch(Throwable $e){
         // Roll back stock and cart changes if anything failed
        try {
            $session->abortTransaction();
        } catch (Throwable $abortError) {
            error_log("Transaction rollback failed: " . $abortError->getMessage());
        }

        throw $e;
    }
}catch(Throwable $error){
     error_log("ADD TO CART ERROR: " . $error->getMessage());

    sendResponse(
        false,
        "Something went wrong while adding the product. Please try again.",
        500,
        "SERVER_ERROR"
    );
}