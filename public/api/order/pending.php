<?php

header("Content-Type: application/json; charset=UTF-8");

// Admin authentication
require_once __DIR__ . "/../../../config/admin_auth.php";

require_once __DIR__ . "/../../../config/database.php";

// retrieve pending orders from mongodb

try{
    $cursor = $orders->find(
        ["status" => "pending"],
        [
            "sort" => ["createdAt" => -1]
        ]
    );

    $orderList = [];

    foreach ($cursor as $order) {
        $orderItems = [];

        foreach ($order["items"] as $item) {
            $orderItems[] = [
                "productId" => (string) $item["productId"],
                "name" => $item["name"],
                "imageUrl" => $item["imageUrl"] ?? null,
                "unitPriceCents" => (int) $item["unitPriceCents"],
                "quantity" => (int) $item["quantity"],
                "lineTotalCents" => (int) $item["lineTotalCents"]
            ];
        }
         $orderList[] = [
            "id" => (string) $order["_id"],
            "createdAt" => $order["createdAt"]
                ->toDateTime()
                ->format(DateTimeInterface::ATOM),
            "items" => $orderItems,
            "totalCents" => (int) $order["totalCents"]
        ];
    }

     http_response_code(200);

    echo json_encode([
        "success" => true,
        "data" => [
            "orders" => $orderList,
            "orderCount" => count($orderList)
        ]
    ]);
}catch(Throwable $error){
      error_log("Pending order retrieval failed: " . $error->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Could not retrieve pending orders."
    ]);
}