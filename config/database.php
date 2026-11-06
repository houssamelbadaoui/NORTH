<?php

require __DIR__ . "/../vendor/autoload.php";

try{
$url = getenv("MONGODB_URL");

$client = new MongoDB\Client($url);

$db = $client->selectDatabase("shopping-app");

$db->command(["ping" => 1]); // test the connection, if not we catch the error and dispaly it

$users = $db->selectCollection("users");
$orders = $db->selectCollection("orders");
$cart = $db->selectCollection("cart");
$products = $db->selectCollection("products");
}catch(Throwable $e){
    echo "Connection failed: " . htmlspecialchars($e->getMessage());
}


