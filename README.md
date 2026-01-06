Inventory Management System

A full-featured Inventory Management System built using pure PHP, MySQL, PDO, Bootstrap, CSS, and JavaScript. The application is designed with a clean architecture, secure authentication, and real-world business rules, focusing on backend robustness while maintaining a responsive and user-friendly interface.

Overview

This system allows authenticated users to manage categories, products, and sales efficiently while maintaining accurate stock levels and data integrity. A centralized dashboard provides quick insights into inventory and sales activity.

The project emphasizes core backend principles such as secure authentication, prepared statements, validation, access control, and modular code organization—without relying on any PHP framework.

Technologies Used

Key Features

Authentication & Security

Secure login and logout system using PHP sessions

Password hashing with password_hash() and verification using password_verify()

Centralized authentication check included via a global header

Protected routes to prevent unauthorized access

Dashboard

Overview dashboard displaying:

Total categories

Total products

Total sales

Quick action buttons to add categories, products, and sales

Category Management

Create, read, update, and delete categories

Server-side validation with error handling

Prevention of deleting categories linked to existing products

Product Management

Full CRUD operations for products

Category-based product association

Stock quantity tracking

Validation for price, stock, and category assignment

Sales Management

Record product sales with quantity validation

Automatic stock deduction on successful sale

Prevention of sales when stock is insufficient

Historical sales listing

UI & UX

Responsive layout using Bootstrap 5

Custom CSS for layout and branding

JavaScript for UI interactions (sidebar toggle, form behavior)

Clean and consistent page structure using reusable header and footer

Tech Stack

Backend: PHP (Pure PHP, no framework)

Database: MySQL

Database Access: PDO with prepared statements

Frontend: Bootstrap 5, CSS, JavaScript

Server: Apache (XAMPP)

Project Structure

inventory-app/
├── auth/                # Login & logout
├── categories/          # Category CRUD
├── products/            # Product CRUD
├── sales/               # Sales management
├── config/              # Database configuration
├── includes/            # Header, footer, auth check
├── public/              # Dashboard
├── database.sql         # Database schema
└── README.md

Database Design

Normalized relational schema

Foreign key relationships between categories, products, and sales

Business rules enforced at application level

Core Tables

users

categories

products

sales

Installation & Setup

Install XAMPP or any Apache + MySQL stack

Clone the repository into htdocs

## Inventory Management System

A lightweight Inventory Management System built with plain PHP, MySQL (PDO), Bootstrap 5 and vanilla JavaScript. It provides secure authentication, category/product CRUD, stock-aware sales processing, and a dashboard with inventory metrics.

### Features
- Secure authentication with PHP sessions and bcrypt password hashing
- Category management (add / edit / delete with safeguards)
- Product management (CRUD, price and stock tracking, category link)
- Sales recording with stock checks, automatic stock deduction, and transaction safety
- Dashboard with quick stats and recent activity
- Responsive UI using Bootstrap 5

### Tech stack
- PHP (no framework)
- MySQL (InnoDB)
- PDO (prepared statements)
- Bootstrap 5, Bootstrap Icons, JavaScript
- XAMPP / Apache for local development

### Project structure

```
inventory-app/
├── auth/                # Login & logout
├── categories/          # Category CRUD (add.php, edit.php, index.php, delete.php)
├── products/            # Product CRUD (add.php, edit.php, index.php, delete.php)
├── sales/               # Sales management (add.php, edit.php, index.php)
├── config/              # Database configuration (db.php)
├── includes/            # Header, footer, auth_check
├── public/              # Dashboard (index.php)
├── database.sql         # Database schema and DDL
└── README.md
```

### Quick setup (local)
1. Install XAMPP (Apache + MySQL + PHP) and start Apache & MySQL.
2. Clone or copy the project into `htdocs` (e.g. `C:\xampp\htdocs\inventory-app`).
3. Create a database (e.g. `inventory_db`) and import `database.sql` via phpMyAdmin or MySQL CLI.
4. Edit database credentials in `config/db.php` to match your local MySQL user and password.
5. Open the app in your browser: `http://localhost/inventory-app/public/`

### Database (schema highlights)
- `categories` (id, name)
- `products` (id, name, price, quantity, category_id) — `category_id` references `categories(id)` (ON DELETE CASCADE)
- `sales` (id, product_id, quantity, sale_date) — `product_id` references `products(id)` (ON DELETE CASCADE)

See `database.sql` for the full DDL.

### How Sales Work (important)
When recording or editing a sale the app does the following in a database transaction:
1. Validate the requested quantity and product selection.
2. Check current stock levels and prevent overselling.
3. Deduct stock from the `products.quantity` column.
4. Insert (or update) the `sales` record with the quantity and timestamp.
5. Commit the transaction — any failure rolls back both stock and sale changes.

Note: Sales are edited (not deleted) to preserve historical records and keep stock consistent.

### Usage notes
- Default admin creation: create an admin directly in the `admins` table or add a small seed script if needed.
- Avoid running raw SQL that bypasses app checks to keep data consistent.

### Development
- Follow the code structure in `includes/` for shared layout and authentication.
- Use prepared statements (`PDO`) for all DB access — this project already follows that pattern.

### Possible enhancements
- Add role-based access control (Admin / Staff)
- Reporting & analytics dashboard
- Pagination, filtering and search on large tables
- Export CSV/PDF of sales and inventory

### Contributing
1. Fork the repo and create a feature branch.
2. Make changes with clear commit messages.
3. Open a PR with a description of your changes.

### License
This repository is for learning and demonstration purposes. Use and modify freely for personal projects.

---

If you'd like, I can also add a small admin seeder, screenshots, or a quick start script next.


Repository Description (GitHub)
