# Cart Optimizer

A Laravel + Vue.js application that intelligently recommends product alternatives in a shopping cart to save money, improve delivery speed, or find in-stock items.

## Overview

This project implements a "Cart Optimization" feature. It analyzes the items in a user's shopping cart and suggests alternatives based on a multi-objective scoring algorithm.

The system considers:
- Price (finding cheaper alternatives)
- Shipping costs (optimizing for lower shipping)
- Delivery speed (finding items that arrive sooner)
- Availability (prioritizing in-stock items over backordered ones)

## Setup Instructions

### Prerequisites
- PHP 8.2+
- Node.js & NPM
- Composer
- SQLite (or another database supported by Laravel)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Christopher-Law/code-test.git
   cd code-test
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   ```

4. Set up the environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Set up the database:
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```
   *Note: The seeder creates a demo user (`demo@example.com` / `password`) and populates the database with products, suppliers, and a sample cart.*

6. Build the frontend assets:
   ```bash
   npm run build
   ```

### Running the Application

You can serve the application using Laravel's built-in server:

```bash
php artisan serve
```

Access the application at `http://localhost:8000`.

Login with the demo credentials:
- **Email:** `demo@example.com`
- **Password:** `password`

Navigate to the **Cart** page to see the optimization features in action.

## Key Improvements & Refactoring

This project demonstrates modern Laravel best practices, SOLID principles, and secure coding standards.

### 1. Architecture & Design Patterns (SOLID)
Logic has been moved out of Controllers and into dedicated classes to follow the **Single Responsibility Principle**.

- **Services**:
  - `CartSummaryService`: Handles cart calculations (totals, tax, shipping) and grouping.
  - `CartOptimizationService`: Handles the core algorithm for finding and scoring product alternatives.
- **Resources**:
  - `CartItemResource`: Transforms `CartItem` models into consistent JSON responses.
- **Configuration**:
  - `config/cart.php` centralizes hardcoded values like tax rates and optimization weights.

### 2. Security & Validation
Robust security measures ensure data integrity and authorization.

- **Form Requests**:
  - `UpdateCartItemQuantityRequest`
  - `CartItemIdsRequest`
  - `ApplyOptimizationRequest`
  - *Strictly validates input types and ensures users can only modify their own cart items.*
- **Authorization**:
  - Implemented strict ownership checks (e.g., `authorizeCartItem`) to prevent IDOR (Insecure Direct Object Reference) vulnerabilities.

### 3. Laravel Best Practices
The codebase follows "The Laravel Way" for readability and maintainability.

- **Eloquent Scopes**:
  - Added reusable query scopes (`CartItem::forUser($id)`, `ProductVariant::active()`) to keep queries clean.
- **Collections**:
  - Replaced raw PHP array functions with Laravel Collection methods (`sortByDesc()`, `count()`, `isEmpty()`).
- **Helpers**:
  - Switched from Facades (`Auth::user()`) to helper functions (`auth()->user()`) for idiomatic code.

## Key Files

### Services
- `app/Services/CartSummaryService.php`
- `app/Services/CartOptimizationService.php`

### Http
- `app/Http/Controllers/CartController.php`
- `app/Http/Controllers/OptimizationController.php`
- `app/Http/Resources/CartItemResource.php`
- `app/Http/Requests/*.php`

### Models
- `app/Models/CartItem.php`
- `app/Models/ProductVariant.php`

### Config
- `config/cart.php`
