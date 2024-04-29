## Requirements

- PHP 8.1
- Composer

## Installation

1. Clone the repository: `git clone https://gitlab.com/icoderz_development/icoderz-demo-laravel/laravel-api.git`
2. Install PHP dependencies: `composer install`
3. Copy the `.env.example` file to `.env` and update the database credentials
4. Generate an application key: `php artisan key:generate`
5. Run the database migrations: `php artisan migrate`
6. Seed the database with sample data: `php artisan db:seed`


## Usage

To start the development server, run `php artisan serve` and visit `http://localhost:8000` in your browser.

To generate the API documentation, run `php artisan l5-swagger:generate` and visit `http://localhost:8000/api/documentation` in your browser.

## License

This starter kit is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
