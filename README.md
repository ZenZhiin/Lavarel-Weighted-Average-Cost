# Purchase and Sales Transaction API

This API allows you to manage products, track purchase and sale transactions, and automatically calculate the cost for each sale. It supports user registration, login, and JWT-based authentication. This documentation provides a detailed guide on how to set up and use the API.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Configuration](#configuration)
  - [Running the Application](#running-the-application)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
  - [Product Endpoints](#product-endpoints)
  - [Transaction Endpoints](#transaction-endpoints)
- [Error Handling](#error-handling)
- [Changelog](#changelog)
- [License](#license)

## Introduction

The Purchase and Sales Transaction API is a Laravel-based RESTful service designed to manage products and their related purchase and sale transactions. It includes the capability to automatically calculate the average cost of each product based on purchase history.

## Features

- **User Authentication:** JWT-based authentication for secure API access.
- **Product Management:** Create, update, and view product details.
- **Transaction Management:** Record purchase and sale transactions, with automatic inventory and cost calculation.
- **Average Cost Calculation:** Automatically calculates the average cost of a product after each purchase.
- **Transaction Tracking:** View all purchase and sale transactions, with details on product cost and inventory changes.

## Getting Started

### Prerequisites

- PHP >= 7.4
- Composer
- Laravel >= 8.x
- MySQL or PostgreSQL database

### Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/your-repo-name.git
    cd your-repo-name
    ```

2. **Install dependencies:**

    ```bash
    composer install
    ```

3. **Create and configure the `.env` file:**

    Copy the `.env.example` to `.env` and update the database credentials and other settings as needed.

    ```bash
    cp .env.example .env
    ```

4. **Generate the application key:**

    ```bash
    php artisan key:generate
    ```

5. **Run database migrations and seeders:**

    ```bash
    php artisan migrate --seed
    ```

6. **Install and publish JWT Authentication:

    ```bash
    composer require tymon/jwt-auth
    php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
    php artisan jwt:secret
    ```

7. **Start the application:

    ```bash
    php artisan serve
    ```

### Configuration

Update the following environment variables in the `.env` file as needed:

- `DB_CONNECTION` - Database connection (e.g., `mysql`)
- `DB_HOST` - Database host (e.g., `127.0.0.1`)
- `DB_PORT` - Database port (e.g., `3306`)
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password
- `JWT_SECRET` - JWT secret key for authentication

### Running the Application

1. **Start the Laravel server:**

    ```bash
    php artisan serve
    ```

2. **Access the API:**

    The API will be available at `http://localhost:3000`.

## Authentication

This API uses JWT-based authentication. You need to obtain a token by registering or logging in and include the token in the `Authorization` header of each request.

### Register

**Endpoint:** `POST /api/register`

**Request Body:**
```json
{
  "name": "Test User",
  "email": "user@test.com",
  "password": "password"
}
```

### Login

**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "yourpassword"
}
```

**Response Body:**
```json
{
  "access_token": "your-jwt-token",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Products

**Endpoint:** `GET /api/products`

**Response Body:**
```json
[
  {
    "id": 1,
    "name": "Product A",
    "price": 10.00,
    "quantity": 50
  },
  ...
]
```

### Transactions

**Endpoint:** `POST /api/transactions`

**Request Body (for purchase):** 
```json
{
  "product_id": 1,
  "quantity": 10,
  "price": 12.50,
  "date": "2024-09-25",
  "type": "purchase"
}
```

**Request Body (for sales):** 
```json
{
  "product_id": 1,
  "quantity": 5,
  "price": 15.00,
  "date": "2024-09-26",
  "type": "sale"
}
```

**Response Body:**
```json
{
  "message": "Transaction created successfully."
}
```

### List transactions

**Endpoint:** `GET /api/transactions`

**Response Body:** 
```json
[
  {
    "product_id": 1,
    "total_quantity": 30,
    "average_price": 11.25,
    "last_transaction_date": "2024-09-26",
    "product": {
      "id": 1,
      "name": "Product A",
      "price": 11.25
    }
  },
  ...
]
```

### Update a transaction

**Endpoint:** `PUT /api/transactions/{id}`

**Request Body:** 
```json
{
  "product_id": 1,
  "quantity": 8,
  "price": 13.00,
  "date": "2024-09-25",
  "type": "purchase"
}
```
**Response Body:** 
```json
{
  "message": "Transaction updated successfully."
}
```

### Delete a transaction 

**Endpoint:** `DELETE /api/transactions/{id}`

**Response Body:** 
```json
{
  "message": "Transaction updated successfully."
}
```