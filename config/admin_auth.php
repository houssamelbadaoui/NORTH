<?php

require_once __DIR__ . "/session.php";

// check if the user is logged in 
if(!isset($_SESSION["user_id"])){
    http_response_code(401);
    exit("Access denied, Please log in.");
}

// check if the user is an administrador
if($_SESSION["role"] !== "admin"){
    http_response_code(403);
    exit("Access denied. Aministrators only.");
}