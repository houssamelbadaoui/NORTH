# NORTH: Online Store

## About the Project

NORTH is a full-stack online store built with PHP, MongoDB, JavaScript, HTML, and CSS. The project focuses on providing a straightforward shopping experience where customers can browse products, manage a shopping cart, and place orders.

The store also includes an administration area where administrators can manage products and stock, review incoming orders, and manage user accounts.

The main idea behind NORTH is to create a simple but functional e-commerce platform, with the essential features needed to manage both the customer shopping experience and the store's day-to-day operations.

## Features

### Customer Features

- Register and log in to an account.
- Browse the available products.
- Add products to the shopping cart.
- Increase or decrease product quantities.
- Remove products from the cart.
- Place orders directly from the shopping cart.
- View previous orders and their current status.

### Administration Features

- Add new products to the store.
- View and manage product inventory.
- Update product stock quantities.
- View pending orders.
- Confirm or cancel orders.
- View registered users and their order counts.
- Change user roles between customer and administrator, subject to account restrictions.

## Order and Inventory Management

NORTH uses a stock reservation approach to keep product inventory consistent when customers place orders.

When a customer adds a product to the cart, the requested quantity is deducted from the available stock. Increasing, decreasing, or removing cart items updates the available stock accordingly.

When an order is submitted, the cart is cleared and an order is created with a `pending` status. The reserved stock remains deducted while the order is waiting for an administrator's decision.

Administrators can either:

- **Confirm the order:** The order status changes to `confirmed`, and the reserved stock remains deducted.
- **Cancel the order:** The order status changes to `cancelled`, and the reserved stock is returned to inventory.

Orders have three possible statuses: `pending`, `confirmed`, and `cancelled`.

## Technologies Used

### PHP

PHP handles the server-side logic, including authentication, API endpoints, database operations, cart management, order processing, and administrative actions.

### MongoDB Atlas

MongoDB Atlas is used as the database. It stores user accounts, products, shopping carts, and orders.

The main collections are:

- `users` — customer and administrator accounts.
- `products` — product information, prices, images, and stock quantities.
- `cart` — shopping cart contents for each customer.
- `orders` — submitted orders and their status.

### Cloudinary

Cloudinary is used for product image management and image hosting. Product image URLs are stored with the product information and used throughout the store.

### JavaScript

Vanilla JavaScript handles frontend interactions and communication with the PHP API. It is used for loading products, updating shopping carts, submitting orders, and managing administrative tables without requiring a frontend framework.

### HTML

HTML provides the structure of the website, including customer pages, product listings, forms, shopping carts, and administration pages.

### CSS

CSS handles the visual design and layout of the application, including shared styles, page-specific styles, responsive layouts, and interactive elements.

### Docker

Docker is used to run the PHP application in a containerized environment. Docker Compose manages the application setup.

## Project Structure

The project is organized into configuration files, the public application, dependencies, and Docker-related files.

```text
NORTH/
│
├── config/
│   ├── admin_auth.php
│   ├── cloudinary.php
│   ├── database.php
│   └── session.php
│
├── public/
│   ├── api/
│   │   ├── cart/
│   │   │   ├── add.php
│   │   │   ├── get.php
│   │   │   ├── remove.php
│   │   │   └── update.php
│   │   │
│   │   ├── order/
│   │   │   ├── add.php
│   │   │   ├── get.php
│   │   │   ├── pending.php
│   │   │   └── update-status.php
│   │   │
│   │   ├── products/
│   │   │   ├── create.php
│   │   │   ├── list.php
│   │   │   └── update-stock.php
│   │   │
│   │   ├── users/
│   │   │   ├── list.php
│   │   │   └── update-role.php
│   │   │
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── register.php
│   │
│   ├── assets/
│   │   ├── css/
│   │   ├── images/
│   │   └── js/
│   │
│   ├── includes/
│   │   └── header.php
│   │
│   ├── pages/
│   │   ├── admin/
│   │   │   ├── manage-orders.php
│   │   │   ├── manage-users.php
│   │   │   └── manageProducts.php
│   │   │
│   │   ├── cart.php
│   │   ├── login.php
│   │   ├── my-orders.php
│   │   ├── products.php
│   │   └── register.php
│   │
│   └── index.php
│
├── vendor/
│
├── .dockerignore
├── .env
├── .gitignore
├── compose.yml
├── composer.json
├── composer.lock
├── Dockerfile
└── README.md
```

### Main Directories and Files

**`config/`**

Contains the application's configuration and shared backend setup.

- `database.php` — connects to MongoDB Atlas and initializes the database collections.
- `cloudinary.php` — configures the Cloudinary integration.
- `session.php` — handles session initialization and authentication-related setup.
- `admin_auth.php` — protects administrative endpoints and pages from unauthorized access.

**`public/`**

Contains the publicly accessible part of the application.

- `index.php` — the store's homepage.
- `api/` — PHP endpoints responsible for handling requests from the frontend.
- `assets/` — CSS stylesheets, JavaScript files, and images.
- `includes/` — reusable page components, such as the website header.
- `pages/` — customer-facing pages and administration pages.

**`public/api/`**

Organizes backend endpoints by feature.

- `cart/` — handles adding, retrieving, updating, and removing cart items.
- `order/` — handles order creation, customer order retrieval, pending orders, and order status updates.
- `products/` — handles product creation, product listing, and stock updates.
- `users/` — handles user listing and role updates.
- The remaining endpoints handle registration, login, and logout.

**`public/assets/`**

Contains the frontend resources:

- `css/` — global and page-specific stylesheets.
- `js/` — scripts responsible for frontend interactions and API communication.
- `images/` — images used by the website.

**`public/pages/`**

Contains the pages customers and administrators interact with. The `admin/` directory separates administrative pages from the customer-facing pages.

**`vendor/`**

Contains PHP dependencies installed through Composer.

**`composer.json` and `composer.lock`**

Define the PHP dependencies used by the project and their installed versions.

**`Dockerfile` and `compose.yml`**

Define the Docker environment and the services used to run the application.

**`.env`**

Contains environment-specific configuration, such as connection strings and service credentials. It should remain private and must not be committed to a public repository.

## Running the Project

### Requirements

- Docker
- Docker Compose
- A MongoDB Atlas database
- A Cloudinary account for image management

### Setup

1. Clone the repository:

   ```bash
   git clone <repository-url>
   ```

2. Navigate to the project directory:

   ```bash
   cd NORTH
   ```

3. Configure the required environment variables in your local `.env` file.

4. Build and start the application:

   ```bash
   docker compose up --build
   ```

5. Open the application in your browser using the local address and port configured in `compose.yml`.

## Project Scope

NORTH focuses on the core functionality of an online store: product browsing, shopping cart management, inventory control, order processing, and user administration.

The current version does not include payment gateway integration, shipping services, or automated order fulfilment. Orders are managed manually through the administration area.

The project is intended as a practical full-stack application, bringing together frontend development, backend programming, database management, and containerized deployment.
