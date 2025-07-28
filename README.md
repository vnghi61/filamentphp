# FilamentPHP - Sales Management System

A sales management system built with Laravel and FilamentPHP, supporting product management, orders, news, and customer management.

## Key Features

- **Product Management**: Categories, brands, units, inventory
- **Order Management**: Online and in-store orders, payment status
- **News Management**: News system with categories
- **User Management**: Admin and customer role permissions
- **Profile Management**: Personal information and password change

## Installation

```bash
# Clone repository
git clone <repository-url>
cd filamentphp

# Install dependencies
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Configure database and run migrations
php artisan migrate --seed

# Start server
php artisan serve
```

## Default Account

- **Email**: admin@example.com
- **Password**: password

## Technologies Used

- Laravel
- FilamentPHP
- MySQL/PostgreSQL
- Tailwind CSS

## Data Structure

- Users
- Categories (Product categories)
- Brands
- Units
- Products
- Orders & OrderItems
- News & NewsCategories