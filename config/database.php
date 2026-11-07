<?php

require __DIR__ . "/../vendor/autoload.php";


$url = getenv("MONGODB_URL");

if (!$url) {
    throw new RuntimeException("MONGODB_URL is not configured.");
}

$client = new MongoDB\Client($url);

$db = $client->selectDatabase("shopping-app");

$db->command(["ping" => 1]); // test the connection, if not we catch the error and dispaly it

$users = $db->selectCollection("users");
$orders = $db->selectCollection("orders");
$cart = $db->selectCollection("cart");
$products = $db->selectCollection("products");



