<?php

header("Content-Type: application/json; charset=UTF-8");

// Admin authentication 
require_once __DIR__ . "/../../../config/admin_auth.php";

// Database connection
require_once __DIR__ . "/../../../config/database.php";

try{
    $cursor = $users->aggregate([
        [
            '$lookup' => [
                'from' => 'orders',
                'let' => [
                    'userIdString' => ['$toString' => '$_id']
                ],
                'pipeline' => [
                    [
                        '$match' => [
                            '$expr' => [
                                '$eq' => ['$userId', '$$userIdString']
                            ]
                        ]
                    ],
                    [
                        '$count' => 'total'
                    ]
                ],
                'as' => 'orderStats'
            ]
        ],
        [
            '$addFields' => [
                'totalOrders' => [
                    '$ifNull' => [
                        ['$arrayElemAt' => ['$orderStats.total', 0]],
                        0
                    ]
                ]
            ]
        ],
        [
            '$sort' => [
                'created_at' => -1
            ]
        ],
        [
            '$project' => [
                '_id' => 1,
                'name' => 1,
                'surname' => 1,
                'email' => 1,
                'role' => 1,
                'totalOrders' => 1
            ]
        ]
    ]);

    $userList = [];
    foreach ($cursor as $user){
        $userList[] = [
             "id" => (string) $user["_id"],
            "fullName" => $user["name"] . " " . $user["surname"],
            "email" => $user["email"],
            "role" => $user["role"],
            "totalOrders" => (int) $user["totalOrders"]
        ];
    }

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "data" => [
            "users" => $userList,
            "userCount" => count($userList)
        ]
    ]);
}catch(Throwable $e){
    error_log("User retrieval failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Could not retrieve users."
    ]);
}