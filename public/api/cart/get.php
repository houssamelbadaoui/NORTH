<?php

require_once __DIR__ . "/../../../config/session.php";

header("Content-Type: application/json; charset=UTF-8");

// helper function response
function sendResponse(bool $success, string $message, int $statusCode = 200, ?string $code = null, array $data = []): void{
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


// Only accept GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    sendResponse(false, "Invalid request method.", 405, "METHOD_NOT_ALLOWED");
}

// check authentication 
if(!isset($_SESSION["user_id"])){
    sendResponse(
        false,
        "Please log in to view your cart.",
        401,
        "AUTH_REQUIRED"
    );
}

// only customer can acceess the cart 
if($_SESSION["role"] !== "customer"){
     sendResponse(
        false,
        "You are not authorized to view the shopping cart.",
        403,
        "FORBIDDEN"
    );
}

// connect to mongodb
try{
    require_once __DIR__ . "/../../../config/database.php";

    // get cutomer ID from session
    $userId = $_SESSION["user_id"];

    // find the customer cart
    $customerCart = $cart->findOne([
        "userId" => $userId
    ]);

    // if customer has no cart, return an empty cart
    if($customerCart === null){
         sendResponse(
            true,
            "Cart retrieved successfully.",
            200,
            null,
            [
                "items" => [],
                "cartCount" => 0,
                "totalCents" => 0
            ]
        );
    }

    // save products ID from the cart 
    $productIds = [];
    foreach($customerCart["items"] as $item){
        $productIds[] = $item["productId"];
    }

    // retrieve all matching products 
    $cursor = $products->find([
        "_id" => ['$in' => $productIds]
    ]);

     // Organize products by their ID for easy lookup
    $productsById = [];

    foreach ($cursor as $product) {
        $id = (string) $product["_id"];
        $productsById[$id] = $product;
    }

    // Prepare the cart response
    $cartItems = [];
    $cartCount = 0;
    $totalCents = 0;

     foreach ($customerCart["items"] as $item) {

        $productId = (string) $item["productId"];
        $quantity = (int) $item["quantity"];

        // Count all units in the cart
        $cartCount += $quantity;

        // Check whether the product still exists
        if (!isset($productsById[$productId])) {

            $cartItems[] = [
                "productId" => $productId,
                "name" => "Product no longer available",
                "imageUrl" => null,
                "unitPriceCents" => null,
                "quantity" => $quantity,
                "lineTotalCents" => null,
                "productAvailable" => false
            ];

            continue;
        }

        // Retrieve the product information
        $product = $productsById[$productId];

        $unitPriceCents = (int) $product["priceCents"];

        // Calculate this product's subtotal
        $lineTotalCents = $unitPriceCents * $quantity;

        // Add this subtotal to the cart total
        $totalCents += $lineTotalCents;

        // Add product information to the response
        $cartItems[] = [
            "productId" => $productId,
            "name" => $product["name"],
            "imageUrl" => $product["imageUrl"],
            "unitPriceCents" => $unitPriceCents,
            "quantity" => $quantity,
            "lineTotalCents" => $lineTotalCents,
            "productAvailable" => true
        ];
    }

      // Return the completed cart
    sendResponse(
        true,
        "Cart retrieved successfully.",
        200,
        null,
        [
            "items" => $cartItems,
            "cartCount" => $cartCount,
            "totalCents" => $totalCents
        ]
    );
}catch(Throwable $e){
    error_log("GET CART ERROR: " . $e->getMessage());

    sendResponse(
        false,
        "Something went wrong while retrieving your cart.",
        500,
        "SERVER_ERROR"
    );
}