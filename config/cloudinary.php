<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Cloudinary\Cloudinary;

$cloudinaryurl = getenv("CLOUDINARY_URL");

if(!$cloudinaryurl){
    throw new RuntimeException("CLOUDINARY_URL is not configured");
}

$cloudinary = new Cloudinary($cloudinaryurl);