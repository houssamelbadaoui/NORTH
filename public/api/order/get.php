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

// Only accept GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    sendResponse(
        false,
        "Invalid request method.",
        405,
        "METHOD_NOT_ALLOWED"
    );
}

// Check authentication
if (!isset($_SESSION["user_id"])) {
    sendResponse(
        false,
        "Please log in to view your orders.",
        401,
        "AUTH_REQUIRED"
    );
}

// Only customers can access their orders
if (($_SESSION["role"] ?? "") !== "customer") {
    sendResponse(
        false,
        "You are not authorized to view these orders.",
        403,
        "FORBIDDEN"
    );
}

try{
    require_once __DIR__ . "/../../../config/database.php";

    $userId = $_SESSION["user_id"]; // get user´s id from session

    // retrieve and sort customer´s orders
    $cursor = $orders->aggregate([
        // first only retrieve the customer´s orders
        [
            '$match' => [
                "userId" => $userId
            ]
        ],
        // Assign a temporary priority to each status
        [
            '$addFields' => [
                "statusPriority" => [
                    '$switch' => [
                        "branches" => [
                            [
                                "case" => ['$eq' => ['$status', 'pending']],
                                "then" => 1
                            ],
                            [
                                "case" => ['$eq' => ['$status', 'confirmed']],
                                "then" => 2
                            ],
                            [
                                "case" => ['$eq' => ['$status', 'cancelled']],
                                "then" => 3
                            ]
                        ],
                        "default" => 4
                    ]
                ]
            ]
        ],
        // Sort by status priority, then newest order first
        [
            '$sort' => [
                "statusPriority" => 1,
                "createdAt" => -1
            ]
        ],

        // Remove the temporary sorting field
        [
            '$project' => [
                "statusPriority" => 0
            ]
        ]
    ]);

    // Format orders for the frontend
    $formattedOrders = [];

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

        $formattedOrders[] = [
            "id" => (string) $order["_id"],
            "status" => $order["status"],
            "createdAt" => $order["createdAt"]
                ->toDateTime()
                ->format(DateTimeInterface::ATOM),
            "items" => $orderItems,
            "totalCents" => (int) $order["totalCents"]
        ];
    }

    sendResponse(
        true,
        "Orders retrieved successfully.",
        200,
        null,
        [
            "orders" => $formattedOrders,
            "orderCount" => count($formattedOrders)
        ]
    );
}catch(Throwable $error){
      error_log("GET ORDERS ERROR: " . $error->getMessage());

    sendResponse(
        false,
        "Something went wrong while retrieving your orders.",
        500,
        "SERVER_ERROR"
    );
}