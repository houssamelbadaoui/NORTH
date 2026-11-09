<?php

// start the session
require_once __DIR__ . "/../../config/session.php";

header("Content-Type: application/json; charset=UTF-8");

// helper function 
function sendResponse(bool $success, string $message, int $statusCode = 200): void{

http_response_code($statusCode);

echo json_encode([
    "success" => $success,
    "message" => $message
]);

exit;
}

// only accept POST request
if($_SERVER["REQUEST_METHOD"] !== "POST"){

sendResponse(false, "Invalid request method.", 405);
}

// clear all session data
$_SESSION = [];

// get the current session cookie
$params = session_get_cookie_params();

// expire the session cookie in the browser 
setcookie(
    session_name(),
    "",
    [
        "expires" => time() - 3600,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"]
    ]
);

// destory the server side session
session_destroy();

sendResponse(true, "Logged out successfully.");