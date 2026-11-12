<?php

header("Content-Type: application/json; charset=UTF-8");


require_once __DIR__ . "/../../../config/admin_auth.php";
require_once __DIR__ . "/../../../config/cloudinary.php";
require_once __DIR__ . "/../../../config/database.php";




// helper function
function sendResponse(int $statusCode, bool $success, string $message): void
{
http_response_code($statusCode);

echo json_encode([
    "success" => $success,
    "message" => $message
]);

exit;
}


// validate that its a post request 
if ($_SERVER["REQUEST_METHOD"] !== "POST"){
    sendResponse(405, false, "Method not allowed");
}

// input 
$name = trim($_POST["name"] ?? "");
$category = trim($_POST["category"] ?? "");
$price = trim($_POST["price"] ?? "");
$stock = trim($_POST["stock"] ?? "");
$description = trim($_POST["description"] ?? "");
$image = $_FILES["image"] ?? null;

// validate 
if($name === "" || $category === "" || $price === "" || $stock === "" || $description === "" || $image === null){
    sendResponse(400, false, "All fields are required");
}

if(strlen($name) > 120){
    sendResponse(400, false, "Product name is too long");
}
// Validate description.
if (strlen($description) > 2000) {
    sendResponse(400, false, "Description is too long.");
}

// validate categories
$allowedCategories = [
    "Jackets",
    "Pants",
    "Shoes",
    "Shirts",
    "Accessories"
];

if (!in_array($category, $allowedCategories, true)) {
    sendResponse(400, false, "Invalid product category.");
}

// Validate price: non-negative, maximum 2 decimal places.
if (!preg_match('/\A(?:0|[1-9]\d*)(?:\.\d{1,2})?\z/', $price)) {
    sendResponse(400, false, "Invalid product price.");
}

// Convert price to integer cents.
$priceParts = explode(".", $price);
$wholePart = (int) $priceParts[0];
$fractionPart = (int) str_pad($priceParts[1] ?? "0", 2, "0");

$priceCents = ($wholePart * 100) + $fractionPart;

if (strlen($price) > 10) {
    sendResponse(400, false, "Price is too high.");
}
if ($wholePart > 1000000 || ($wholePart === 1000000 && $fractionPart > 0)) {
    sendResponse(400, false, "Maximum product price is $1,000,000.00.");
}
// Validate stock.
$stockValue = filter_var($stock, FILTER_VALIDATE_INT);

if ($stockValue === false || $stockValue < 0) {
    sendResponse(400, false, "Stock must be a non-negative integer.");
}

$stock = $stockValue;

// validate image upload status 
if($image["error"] !== UPLOAD_ERR_OK){
    sendResponse(400, false, "Image upload failed.");
}
if(!is_uploaded_file($image["tmp_name"])){
    sendResponse(400, false, "Invalid image upload.");
}
// allow images up to 5MB
$maxFileSize = 5 * 1024 * 1024;
if($image["size"] > $maxFileSize){
    sendResponse(400, false, "Image must not exceed 5MB.");
}

// validate image type 
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($image["tmp_name"]);

$allowedTypes = [
    "image/jpeg",
    "image/png",
    "image/webp"
];

if(!in_array($mimeType, $allowedTypes, true)){
    sendResponse(400, false, "Only JPEG, PNG, and WebP images are allowed.");
}


$imageUrl = null;
$publicId = null;
// upload the image file to cloudinary 
try{
    $uploadResult = $cloudinary->uploadApi()->upload(
        $image["tmp_name"],
        [
            "folder" => "north/products",
            "resource_type" => "image"
        ]
    );
    $imageUrl = $uploadResult["secure_url"];
    $publicId = $uploadResult["public_id"];
}catch(Throwable $e){
    error_log("Cloudinary upload failed: " . $e->getMessage());

    sendResponse(500, false, "Could not upload product image.");
}


// we save our product to Mongodb
try{

    $now = new MongoDB\BSON\UTCDateTime();

    $product = [
         "name" => $name,
        "category" => $category,
        "description" => $description,
        "priceCents" => $priceCents,
        "stock" => $stock,
        "imageUrl" => $imageUrl,
        "cloudinaryPublicId" => $publicId,
        "active" => true,
        "createdAt" => $now,
        "updatedAt" => $now
    ];

    $insertResult = $products->insertOne($product);
}catch(Throwable $e){
    error_log("Product creation failed: " . $e->getMessage());

    // remove the uploaded image if the databse insert fails 
    try{
        $cloudinary->uploadApi()->destroy($publicId);
    }catch(Throwable $error){
        error_log("Cloudinary cleanup failed: " . $error->getMessage());
    }

    sendResponse(500, false, "Could not save the product");
}

sendResponse(201, true, "Product created successfully");