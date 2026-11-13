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