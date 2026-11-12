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
    <link rel="stylesheet" href="../../assets//css//global.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets//css//manageProducts.css">
</head>
<body>
    <?php require_once __DIR__ . "/../../includes/header.php"; ?>

    <main class="manage-products">

    <!-- Page heading -->
     <section class="products-header">
        <div class="products-header__text">
             <h1>Manage Products</h1>
             <p>Manage your store inventory and product listings.</p>
        </div>
        <button type="button" id="addProductButton" class="button-primary"> + Add Product</button>
     </section>
       

<!-- products table -->
 <section class="products-section">

            <div class="products-table-wrapper">

                <table class="products-table">

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

                    <tbody id="productsTableBody">
                        <!-- Products will be rendered here by JavaScript -->
                    </tbody>

                </table>

            </div>

        </section>


 <!-- Add product modal -->

  <!-- Add product modal -->
        <dialog id="addProductDialog" class="product-dialog">

            <div class="product-dialog__header">
                <h2>Add a Product</h2>
                <p>Enter the details of your new product.</p>
            </div>

            <form id="addProductForm" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="productName">Product name</label>
                    <input
                        type="text"
                        id="productName"
                        name="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="productCategory">Category</label>
                    <select id="productCategory" name="category" required>
                        <option value="">Choose a category</option>
                        <option value="Jackets">Jackets</option>
                        <option value="Pants">Pants</option>
                        <option value="Shoes">Shoes</option>
                        <option value="Shirts">Shirts</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="productPrice">Price ($)</label>
                    <input
                        type="number"
                        id="productPrice"
                        name="price"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="productStock">Stock</label>
                    <input
                        type="number"
                        id="productStock"
                        name="stock"
                        min="0"
                        step="1"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="productImage">Product image</label>
                    <input
                        type="file"
                        id="productImage"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >
                    <small>JPEG, PNG or WebP. Maximum size: 5 MB.</small>
                </div>

                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea
                        id="productDescription"
                        name="description"
                        rows="4"
                        required
                    ></textarea>
                </div>

                <div class="product-dialog__actions">
                    <button type="button" id="cancelAddProductButton" class="button-secondary">
                        Cancel
                    </button>

                    <button type="submit" id="buttonSubmit" class="button-primary">
                        Save Product
                    </button>
                </div>

            </form>

        </dialog>
    </main>
    <script src="../../assets/js/manageProducts.js" defer></script>
    <script src="../../assets/js/logout.js" defer></script>
</body>
</html>