<?php

// session
require_once __DIR__ . "/../../config/session.php";

header("content-Type: application/json; charset-UTF-8");

// helper function 
function sendResponse(bool $success, string $message, int $statusCode = 200): void{

http_response_code($statusCode);

echo json_encode([
    "success" => $success,
    "message" => $message
]);

exit;
}

// only accept post request, if its not, not process
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    sendResponse(false, "Invalid request method", 405);
}

// data
$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";

// validate 
if($email === "" || $password === ""){
    sendResponse(false, "All fields required", 400);
}

// validate the mail 
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    sendResponse(false, "Please enter a valid email address", 400);
}

// validate password
if(strlen($password) < 8){
    sendResponse(false, "Password must contain at least 8 characters.", 400);
}

// find user in the database
try{
    require_once __DIR__ . "/../../config/database.php";

    // find user
    $user = $users->findOne([
        "email" => $email
    ]); 

    // if not found 
    if($user === null){
        sendResponse(false, "Invalid email or password", 401);
    }

    // verify the password 
    if(!password_verify($password, $user["password"])){
         sendResponse(false, "Invalid email or password", 401);
    }

    // generate a new session ID
    session_regenerate_id(true);

    $_SESSION["user_id"] = (string) $user["_id"];
    $_SESSION["name"] = $user["name"];
    $_SESSION["role"] = $user["role"];

    sendResponse(true, "Welcome Back");

}catch(Throwable $e){
    error_log("LOGIN ERROR: " . $e->getMessage());
    sendResponse(false, "Something went wrong, Please try again", 500);
}