# Inventory & Order Management API

A RESTful API built with Laravel for managing products and orders, featuring stock management, pessimistic locking for concurrency, and comprehensive feature tests.

## Requirements

- PHP 8.1+
- Composer
- MySQL 8.0+

## Setup Instructions

1. Clone or copy the project files to your local machine.
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy the `.env.example` to `.env` and configure your environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Update the `.env` file with your MySQL database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=inventory_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

## Database Migration & Seeding

To run the migrations and set up the tables:
```bash
php artisan migrate
```

*(Optional)* If you have seeders set up, you can run:
```bash
php artisan db:seed
```

## Running the Application

Start the local Laravel development server:
```bash
php artisan serve
```
The API will be accessible at `http://localhost:8000/api`.

## Running Tests

The application includes feature tests covering core business logic, including stock adjustments and order cancellations.

To run the PHPUnit tests:
```bash
php artisan test
```

## API Endpoints & Example Payloads

All endpoints accept and return `application/json`.

### Products

**Create a new product**
- `POST /api/products`
```json
{
  "name": "Laptop",
  "description": "A powerful laptop",
  "price": 999.99,
  "stock_quantity": 10
}
```

**List all products**
- `GET /api/products`

**Get a single product**
- `GET /api/products/{id}`

**Update product details**
- `PUT /api/products/{id}`
```json
{
  "price": 899.99
}
```

**Update stock quantity**
- `PATCH /api/products/{id}/stock`
```json
{
  "quantity": 15
}
```

### Orders

**Create a new order** (Automatically decrements stock)
- `POST /api/orders`
```json
{
  "customer_name": "John Doe",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

**List all orders**
- `GET /api/orders`

**View a single order with items**
- `GET /api/orders/{id}`

**Cancel an order and restore stock**
- `POST /api/orders/{id}/cancel`

## Notes
- The API uses database transactions and pessimistic locking (`lockForUpdate()`) to handle concurrent stock modifications safely.
- Proper HTTP status codes are returned (201 for creation, 422 for validation/stock issues, 409 for conflicts, etc.).
