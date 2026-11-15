<?php

header("Content-Type: application/json; charset=UTF-8");

// Only admins can update order statuses
require_once __DIR__ . "/../../../config/admin_auth.php";

require_once __DIR__ . "/../../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);

    exit;
}

// Read JSON request
$data = json_decode(file_get_contents("php://input"), true);

$orderId = $data["orderId"] ?? "";
$newStatus = $data["status"] ?? "";

// Only these two status changes are allowed
if (!in_array($newStatus, ["confirmed", "cancelled"], true)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid order status."
    ]);

    exit;
}

$mongoSession = $client->startSession();


try {
    $mongoSession->startTransaction();

    $orderObjectId = new MongoDB\BSON\ObjectId($orderId);

    // Find the order only if it is still pending
    $order = $orders->findOne(
        [
            "_id" => $orderObjectId,
            "status" => "pending"
        ],
        [
            "session" => $mongoSession
        ]
    );

    if (!$order) {
        $mongoSession->abortTransaction();
        

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "Order not found or is no longer pending."
        ]);

        exit;
    }

    // Restore reserved stock if the order is cancelled
    if ($newStatus === "cancelled") {
        foreach ($order["items"] as $item) {
            $stockResult = $products->updateOne(
                [
                    "_id" => $item["productId"]
                ],
                [
                    '$inc' => [
                        "stock" => (int) $item["quantity"]
                    ]
                ],
                [
                    "session" => $mongoSession
                ]
            );

            // Do not cancel if a product cannot be found
            if ($stockResult->getMatchedCount() !== 1) {
                throw new RuntimeException(
                    "Could not restore stock for an order product."
                );
            }
        }
    }

    // Update the order status
    $updateResult = $orders->updateOne(
        [
            "_id" => $orderObjectId,
            "status" => "pending"
        ],
        [
            '$set' => [
                "status" => $newStatus,
                "updatedAt" => new MongoDB\BSON\UTCDateTime()
            ]
        ],
        [
            "session" => $mongoSession
        ]
    );

    if ($updateResult->getMatchedCount() !== 1) {
        throw new RuntimeException("Could not update order status.");
    }

    $mongoSession->commitTransaction();

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => $newStatus === "confirmed"
            ? "Order confirmed successfully."
            : "Order cancelled and stock restored successfully."
    ]);

} catch (Throwable $e) {
        $mongoSession->abortTransaction();

    error_log("Order status update failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Could not update the order."
    ]);

} finally {
    $mongoSession->endSession();
}