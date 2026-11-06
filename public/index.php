<?php

require __DIR__ . '/../vendor/autoload.php';

try{
    $client = new MongoDB\Client(getenv('MONGODB_URL'));
    $client->selectDatabase("shopping-app")->command(['ping' => 1]);
    echo 'Connected to MongoDB Atlas ';
} catch (Throwable $e) {
    echo 'Connection failed: ' . htmlspecialchars($e->getMessage());
}