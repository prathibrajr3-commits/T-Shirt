# Walkthrough: AuraWear T-Shirt E-Commerce Website

We have successfully built a premium, modern, and high-performance **T-Shirt E-Commerce Website (AuraWear)** using **PHP 8.3 + Laravel 11** and a SQLite database. 

The website is fully functional, styled with a high-end dark glassmorphism theme, and covers all required customer-facing and administrative workflows.

---

## 1. Features Implemented

### User (Storefront) Side
* **Custom Authentication System**: Register and login forms with custom validation. Includes seed logins for easy testing.
* **Responsive Product Catalog**: Browse collections, search by text, filter by category (Men, Women, Oversized, Printed), and sort by price or newest arrivals.
* **Premium Product Details Page**: Dynamic size and color radio selectors, active inventory stock count alerts, related product suggestion grids, and a user-generated ratings and reviews section.
* **Session-Based Shopping Cart**: Add, update quantities, remove items, and see calculations for estimated shipping costs (free for orders over $50).
* **Secure Checkout Process**: Fill in delivery address and contact phone, select Payment Method (Cash on Delivery or Simulated Card Transaction), and finalize the order with automated inventory updates.
* **Stepped Order Tracking**: Visual tracking stepper (Pending -> Processing -> Shipped -> Delivered) to let customers monitor shipping status, along with carrier tracking numbers when available.
* **Profile Management**: Update user credentials, shipping address, phone contact, or change account password.

### Admin Side
* **Admin Analytics Dashboard**: Core KPIs (Total Sales Revenue, Total Orders count, Pending/Processing queues), live out-of-stock and low-stock inventory indicators, and logs of the latest customer reviews and orders.
* **Categories Management CRUD**: Add new product categories, edit details, and view item counts. Includes safeguards preventing deletion of categories containing active products.
* **Products Inventory CRUD**: Full CRUD capability including direct local image uploads, description editor, base vs. discount pricing, and formatting sizing/coloring choices to JSON lists.
* **Order Management Control**: View order details, transition delivery statuses, update simulated transaction status, and input tracking numbers.

---

## 2. Code Architecture & Files Created

* **Database Migrations**:
  - Custom fields in [users](file:///e:/T-Shirt/database/migrations/0001_01_01_000000_create_users_table.php) for roles, phone, address.
  - [categories](file:///e:/T-Shirt/database/migrations/2026_06_12_041001_create_categories_table.php)
  - [products](file:///e:/T-Shirt/database/migrations/2026_06_12_041002_create_products_table.php)
  - [orders](file:///e:/T-Shirt/database/migrations/2026_06_12_041002_create_orders_table.php)
  - [order_items](file:///e:/T-Shirt/database/migrations/2026_06_12_041003_create_order_items_table.php)
  - [reviews](file:///e:/T-Shirt/database/migrations/2026_06_12_041003_create_reviews_table.php)
* **Eloquent Models**:
  - [User](file:///e:/T-Shirt/app/Models/User.php) (with `isAdmin()` helper and relations)
  - [Category](file:///e:/T-Shirt/app/Models/Category.php)
  - [Product](file:///e:/T-Shirt/app/Models/Product.php) (with active price logic)
  - [Order](file:///e:/T-Shirt/app/Models/Order.php)
  - [OrderItem](file:///e:/T-Shirt/app/Models/OrderItem.php)
  - [Review](file:///e:/T-Shirt/app/Models/Review.php)
* **Controllers**:
  - Login/Register/Logout: [LoginController](file:///e:/T-Shirt/app/Http/Controllers/Auth/LoginController.php), [RegisterController](file:///e:/T-Shirt/app/Http/Controllers/Auth/RegisterController.php), [LogoutController](file:///e:/T-Shirt/app/Http/Controllers/Auth/LogoutController.php)
  - Storefront: [ShopController](file:///e:/T-Shirt/app/Http/Controllers/ShopController.php), [CartController](file:///e:/T-Shirt/app/Http/Controllers/CartController.php), [CheckoutController](file:///e:/T-Shirt/app/Http/Controllers/CheckoutController.php), [OrderController](file:///e:/T-Shirt/app/Http/Controllers/OrderController.php), [ProfileController](file:///e:/T-Shirt/app/Http/Controllers/ProfileController.php), [ReviewController](file:///e:/T-Shirt/app/Http/Controllers/ReviewController.php)
  - Admin: [AdminDashboardController](file:///e:/T-Shirt/app/Http/Controllers/Admin/AdminDashboardController.php), [AdminCategoryController](file:///e:/T-Shirt/app/Http/Controllers/Admin/AdminCategoryController.php), [AdminProductController](file:///e:/T-Shirt/app/Http/Controllers/Admin/AdminProductController.php), [AdminOrderController](file:///e:/T-Shirt/app/Http/Controllers/Admin/AdminOrderController.php)
* **Design & Styling**:
  - Obsidian dark layout and blur filters: [style.css](file:///e:/T-Shirt/public/css/style.css)

---

## 3. Verification & Performance Checks

1. **Database Schema & Seeders**:
   - Dropped existing tables and migrated successfully.
   - Seeded 8 premium T-Shirts using generated assets matching description details (Cyberpunk, Minimalist, Retro, Anime).
2. **App Execution**:
   - The web server is actively running on `http://127.0.0.1:8000`.
   - Verified active pages (e.g. details page, shop collections) load and style correctly.

---

## 4. Visual Showcase

Here is a preview of the homepage design:

![AuraWear Homepage Categories and Arrivals](C:/Users/DELL7420/.gemini/antigravity-ide/brain/4b7d14ef-094e-4f78-8e3d-df969ebfa113/homepage_categories_and_arrivals_1781239222651.png)

A screen recording of the verification is available at:
![AuraWear Visual Walkthrough](C:/Users/DELL7420/.gemini/antigravity-ide/brain/4b7d14ef-094e-4f78-8e3d-df969ebfa113/aurawear_showcase_1781239144924.webp)

