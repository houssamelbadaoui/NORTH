<?php

// start session 
require_once __DIR__ . "/../../config/session.php";

header("content-Type: application/json; charset-UTF-8");


// a function that send result to js
function sendResult(bool $success, string $message, int $statusCode = 200):void
{
    http_response_code($statusCode);
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);

    exit;
}

// only accept post request, if its not, not process
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    sendResult(false, "Invalid request method", 405);
}

// read the submitted values 
$name = trim($_POST["name"] ?? "");
$surname = trim($_POST["surname"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$confirmedPassword = $_POST["confirmPassword"] ?? "";

// validation 

// all fields are obligatory
if($name === "" || $surname === "" || $email === "" || $password === "" || $confirmedPassword === ""){
    sendResult(false, "Please complete all fields", 400);
}

// validate length of input 
if(mb_strlen($name) < 2 || mb_strlen($name) > 50){
    sendResult(false, "Name must be between 2 and 50 charachters", 400);
}
if(mb_strlen($surname) < 2 || mb_strlen($surname) > 50){
    sendResult(false, "Surname must be between 2 and 50 charachters", 400);
}

// validate the mail 
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    sendResult(false, "Please enter a valid email address", 400);
}

// validate password
if(strlen($password) < 8){
    sendResult(false, "Password must contain at least 8 characters.", 400);
}

if($password !== $confirmedPassword){
    sendResult(false, "Passwords do not match.", 400);
}

// insert user into database
try{
    require_once __DIR__ . "/../../config/database.php";

    // we check if users exists 
    $existUser = $users->findOne([
        "email" => $email
    ]);

    if($existUser !== null){
        sendResult(false, "An account with this email already exists.", 409);
    }

    // hash the password to store it in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // insert the new user
    $result = $users->insertOne([
        "name" => $name,
        "surname" => $surname,
        "email" => $email,
        "password" => $hashedPassword,
        "role" => "customer",
        "created_at" => new MongoDB\BSON\UTCDateTime()
    ]);

    // create an authenticated session 
    session_regenerate_id(true);

    $_SESSION["user_id"] = (string) $result->getInsertedId();
    $_SESSION["name"] = $name;
    $_SESSION["role"] = "customer";

    sendResult(true, "Your NORTH account has been created succefully");
}catch(Throwable $error){
    sendResult(false, "We could not create your account, Please try again later.", 500);
}