<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../../config/database.php";

// retrieve active products from Mongodb
try{
     $cursor = $products->find(
        ["active" => true],
        [
            "sort" => ["createdAt" => -1],
            "projection" => [
                "cloudinaryPublicId" => 0
            ]
        ]
    );

    $productList = [];

    foreach($cursor as $product){
        $productList[] = [
            "id" => (string) $product["_id"],
            "name" => $product["name"],
            "category" => $product["category"],
            "description" => $product["description"],
            "priceCents" => $product["priceCents"],
            "stock" => $product["stock"],
            "imageUrl" => $product["imageUrl"]
        ];
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => [
            "products" => $productList
        ]
    ]);
}catch(Throwable $e){
    error_log("Product retrieval failed: " . $e->getMessage());

      http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Could not retrieve products."
    ]);
}