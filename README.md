## Features
- User CRUD operations for various entities
- API documentation with Swagger
- Test cases

## Requirements

- PHP 8.1
- Composer

## Installation

1. Clone the repository: `git clone https://github.com/iCoderzDev/laravel-homework-task-ashishsudra.git`
2. Install PHP dependencies: `composer install`
3. Copy the `.env.example` file to `.env` and update the database credentials
    Replace DB connection
    DB_CONNECTION=pgsql
    DB_HOST=hansken.db.elephantsql.com
    DB_PORT=5432
    DB_DATABASE=pvkbeqgd
    DB_USERNAME=pvkbeqgd
    DB_PASSWORD=elt_vDYoSh8dOnj5dEfOwPgSw2nAcc43
4. Generate an application key: `php artisan key:generate`
5. Run the database migrations: `php artisan migrate`
6. Seed the database with sample data: `php artisan db:seed`
7. API Collection: `https://documenter.getpostman.com/view/15937310/2sA3BuXV5p`


## Usage

To start the development server, run `php artisan serve` and visit `http://localhost:8000` in your browser.

To generate the API documentation, run `php artisan l5-swagger:generate` and visit `http://localhost:8000/api/documentation` in your browser.

To run Test case  `php artisan test`

