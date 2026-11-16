<?php

header("Content-Type: application/json; charset=UTF-8");

// admin authentication 
require_once __DIR__ . "/../../../config/admin_auth.php";

// database connection
require_once __DIR__ . "/../../../config/database.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);
    exit;
}

// read json data 
$data = json_decode(file_get_contents("php://input"));

// Validate request data
if (
    !$data ||
    !isset($data->productId) ||
    !isset($data->stock) ||
    !is_string($data->productId) ||
    !is_int($data->stock) ||
    $data->stock < 0
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid product ID or stock quantity."
    ]);
    exit;
}

try{
    $productObjectId = new MongoDB\BSON\ObjectId($data->productId); // transform productId into mongodb object

    // update product stock 
    $result = $products->updateOne(
        ["_id" => $productObjectId],
        ['$set' => [
            "stock" => $data->stock
        ]]
    );

    // validate 
    if($result->getMatchedCount() === 0){
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found."
        ]);
        exit;
    }

    // Successful update
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Product stock updated successfully."
    ]);
}catch(Throwable $e){
    error_log("Product stock update failed: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not update product stock."
    ]);
}
