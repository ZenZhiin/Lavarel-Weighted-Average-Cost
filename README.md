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
