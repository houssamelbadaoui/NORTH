<?php

header("Content-Type: application/json; charset=UTF-8");

// Admin authentication
require_once __DIR__ . "/../../../config/admin_auth.php";

// Database connection
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

// Read json data 
$data = json_decode(file_get_contents("php://input"));

try {

    $userObjectId = new MongoDB\BSON\ObjectId($data->userId);

    // Find the target user
    $user = $users->findOne([
        "_id" => $userObjectId
    ]);

    if ($user === null) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "User not found."
        ]);
        exit;
    }

    // Check the user's actual role
    $currentRole = $user["role"] ?? null;

    // Prevent the logged-in admin from changing their own role
    if ((string) $user["_id"] === $_SESSION["user_id"]) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "You cannot change your own role."
        ]);
        exit;
    }

    // Determine the new role
    $newRole = $currentRole === "customer" ? "admin" : "customer";

    // Prevent demoting the last remaining admin
    if ($currentRole === "admin") {

        $adminCount = $users->countDocuments([
            "role" => "admin"
        ]);

        if ($adminCount <= 1) {
            http_response_code(403);
            echo json_encode([
                "success" => false,
                "message" => "You cannot demote the last admin."
            ]);
            exit;
        }
    }

    // Update role only if it still matches the role we read
    $result = $users->updateOne(
        [
            "_id" => $userObjectId,
            "role" => $currentRole
        ],
        [
            '$set' => [
                "role" => $newRole
            ]
        ]
    );

    if ($result->getMatchedCount() === 0) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "User role has changed. Please refresh and try again."
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "User role updated successfully.",
        "data" => [
            "role" => $newRole
        ]
    ]);

} catch (Throwable $e) {

    error_log("User role update failed: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not update user role."
    ]);
}