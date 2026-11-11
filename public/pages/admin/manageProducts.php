<?php

// admin authentication.
require_once __DIR__ . "/../../../config/admin_auth.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | NORTH</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . "/../../includes/header.php"; ?>

    <main>
        <h1>Manage Products</h1>

        <button type="button" id="addProductButton">Add Product</button>

<!-- products table -->
 <table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="productsTableBody"></tbody>
 </table>

 <!-- Add product modal -->

 <dialog id="addProductDialog">
    <h2>Add a Product</h2>
    <form id="addProductForm" enctype="multipart/form-data">
          <label for="productName">Product name</label>
                <input
                    type="text"
                    id="productName"
                    name="name"
                    required
                >

                <label for="productCategory">Category</label>
                <select
                    id="productCategory"
                    name="category"
                    required
                >
                    <option value="">Choose a category</option>
                    <option value="Jackets">Jackets</option>
                    <option value="Pants">Pants</option>
                    <option value="Shoes">Shoes</option>
                    <option value="Shirts">Shirts</option>
                    <option value="Accessories">Accessories</option>
                </select>

                <label for="productPrice">Price</label>
                <input
                    type="number"
                    id="productPrice"
                    name="price"
                    min="0"
                    step="0.01"
                    required
                >

                <label for="productStock">Stock</label>
                <input
                    type="number"
                    id="productStock"
                    name="stock"
                    min="0"
                    step="1"
                    required
                >

                <label for="productImage">Product image</label>
                <input
                    type="file"
                    id="productImage"
                    name="image"
                    accept="image/*"
                    required
                >

                <label for="productDescription">Description</label>
                <textarea
                    id="productDescription"
                    name="description"
                    required
                ></textarea>

                <button type="submit" id="buttonSubmit">Save Product</button>
                <button type="button" id="cancelAddProductButton">
                    Cancel
                </button>

    </form>
 </dialog>
    </main>
    <script src="../../assets/js/manageProducts.js" defer></script>
    <script src="../../assets/js/logout.js" defer></script>
</body>
</html>