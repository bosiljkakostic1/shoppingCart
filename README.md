# Shopping Cart Application

A full-stack shopping cart application built with Laravel (PHP) backend and React (TypeScript) frontend using Inertia.js.

## Features

- **Product Management**: View products with real-time stock quantities
- **Shopping Cart**: Add products to cart, update quantities, and manage cart items
- **Stock Management**: Dynamic stock calculation based on product inputs and cart items
- **Low Stock Notifications**: Automated email alerts when products reach minimum stock levels
- **Daily Sales Reports**: Scheduled daily reports sent via email
- **User Authentication**: Secure user authentication with Laravel Fortify
- **Responsive Design**: Modern UI built with Tailwind CSS and Radix UI components

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP**: >= 8.2 (tested with PHP 8.3.6)
- **Composer**: Latest version (for PHP dependency management)
- **Node.js**: >= 20.x (tested with Node.js 20.19.6)
- **npm**: Comes with Node.js (or use yarn)
- **SQLite**: For database (included with PHP, or use MySQL/PostgreSQL)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/bosiljkakostic1/shoppingCart.git
cd shoppingCart
```

### 2. Install PHP Dependencies

```bash
composer install
```

This will install all Laravel and PHP dependencies defined in `composer.json`.

### 3. Install Node.js Dependencies

```bash
npm install
```

This will install all frontend dependencies including React, TypeScript, Tailwind CSS, and other UI libraries.

### 4. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 5. Database Setup

The application uses SQLite by default. Create the database file:

```bash
touch database/database.sqlite
```

Or update `.env` to use MySQL/PostgreSQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopping_cart
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed the database:

```bash
php artisan migrate --seed
```

This will:
- Create all necessary tables (products, shoppingCarts, shoppingCartProducts, productInputs)
- Seed the database with 20 sample products

### 6. Mail Configuration

The application sends emails for low stock notifications and daily sales reports. Configure your mail settings in `.env`:

#### For Development (Log Driver - Recommended)

Emails will be logged to `storage/logs/laravel.log`:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### For Production (SMTP)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### For Gmail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Note**: For Gmail, you'll need to generate an [App Password](https://support.google.com/accounts/answer/185833) instead of your regular password.

### 7. Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

## Running the Application

### Development Mode (Recommended)

The easiest way to run the application in development is using the provided composer script which starts all necessary services concurrently:

```bash
composer run dev
```

**OR** if you prefer npm:

```bash
npm run dev
```

**Note**: `composer run dev` starts everything including the scheduler, while `npm run dev` only starts Vite. Use `composer run dev` for full development setup.

The `composer run dev` command will start:
- Laravel development server (http://localhost:8000)
- Queue worker (for processing jobs)
- Schedule worker (for running scheduled tasks like daily sales reports)
- Log viewer (Pail)
- Vite dev server (for hot module replacement)

**To restart after interruption:**
Simply run `composer run dev` again. All services will restart automatically.

**To stop all services:**
Press `Ctrl+C` in the terminal where `composer run dev` is running. This will stop all services gracefully.

### Manual Setup

If you prefer to run services separately:

#### Terminal 1: Start Laravel Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

#### Terminal 2: Start Queue Worker

```bash
php artisan queue:work
```

This is required for processing:
- Low stock notification jobs
- Daily sales report jobs

#### Terminal 3: Start Vite Dev Server (for development)

```bash
npm run dev
```

#### Terminal 4: Run Scheduled Tasks (for development)

For development/testing, run the schedule worker:

```bash
php artisan schedule:work
```

This will run scheduled tasks (like daily sales reports) according to their schedule.

**For production**, add this to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Note**: If you're using `composer run dev`, the schedule worker is already included and you don't need to run it separately.

## Queue Configuration

The application uses the database queue driver by default. Make sure:

1. Queue worker is running: `php artisan queue:work`
2. Jobs table exists (created by migrations)
3. Failed jobs table exists (created by migrations)

To view failed jobs:

```bash
php artisan queue:failed
```

To retry failed jobs:

```bash
php artisan queue:retry all
```

## Scheduled Tasks

The application includes a scheduled task for daily sales reports:

- **Command**: `sales:daily-report`
- **Schedule**: Runs daily at 6:00 PM UTC
- **Purpose**: Generates and emails daily sales report to admin

To test manually:

```bash
php artisan sales:daily-report
```

## Database Structure

### Tables

- **products**: Product information (name, price, minStockQuantity, unit)
- **productInputs**: Tracks product stock additions
- **shoppingCarts**: User shopping carts (state: active, ordered, canceled, payed)
- **shoppingCartProducts**: Cart items linking products to carts
- **users**: User accounts
- **jobs**: Queue jobs table
- **failed_jobs**: Failed queue jobs

### Stock Calculation

Stock quantity is calculated dynamically:
```
Available Quantity = Sum(productInputs.addedQuantity) - Sum(shoppingCartProducts.quantity)
```

This includes items in all cart states (active, ordered, etc.).

## Features Explained

### Low Stock Notifications

- Triggered automatically when a product's available quantity drops to or below `minStockQuantity`
- Email sent to `admin@example.com` (auto-created if doesn't exist)
- Includes duplicate prevention (1-hour cache to prevent spam)
- Triggered when:
  - Products are added to cart
  - Cart quantities are updated
  - Products are removed from cart
  - New product inputs are added

### Daily Sales Report

- Runs automatically every evening at 6:00 PM UTC
- Collects all orders from the current day
- Reports:
  - Total revenue
  - Total items sold
  - Total orders
  - Per-product breakdown
- Emailed to `admin@example.com`

## Testing

Run PHP tests:

```bash
php artisan test
```

Run linting:

```bash
composer lint
npm run lint
```

## Troubleshooting

### Queue Jobs Not Processing

1. Ensure queue worker is running: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Verify database connection and jobs table exists

### Emails Not Sending

1. Check `.env` mail configuration
2. For log driver, check `storage/logs/laravel.log`
3. Verify queue worker is running (emails are queued)
4. Check failed jobs table for errors

### Database Issues

1. Ensure database file exists: `database/database.sqlite`
2. Run migrations: `php artisan migrate`
3. Check database permissions

### Frontend Not Loading

1. Ensure Vite is running: `npm run dev`
2. Check browser console for errors
3. Clear cache: `php artisan cache:clear`
4. Rebuild assets: `npm run build`

## Project Structure

```
shoppingCart/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/Controllers/     # API controllers
│   ├── Jobs/                 # Queue jobs
│   ├── Mail/                 # Mailable classes
│   └── Models/               # Eloquent models
├── database/
│   ├── migrations/           # Database migrations
│   ├── seeders/              # Database seeders
│   └── database.sqlite       # SQLite database (gitignored)
├── resources/
│   ├── js/
│   │   ├── components/       # React components
│   │   ├── layouts/         # Layout components
│   │   └── pages/           # Inertia pages
│   └── views/
│       └── mail/             # Email templates
├── routes/
│   ├── web.php              # Web routes
│   └── console.php          # Scheduled tasks
└── public/                  # Public assets
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## API Documentation

For detailed API endpoint documentation, see [API_ENDPOINTS.md](API_ENDPOINTS.md).

### Postman Collection

A Postman collection is included for easy API testing:

1. **Collection**: `postman/Shopping Cart API.postman_collection.json`
2. **Environment**: `postman/Local Environment.postman_environment.json`

To use:
1. Import both files into Postman
2. Set environment variables (user_email, user_password)
3. Start with the Login request to authenticate
4. Use other endpoints with the CSRF token

### Available Endpoints

**Authentication** (Laravel Fortify):
- `POST /register` - Register new user
- `POST /login` - Login user
- `POST /logout` - Logout user

**Products**:
- `GET /api/products` - Get all products
- `GET /api/products/{id}` - Get product by ID
- `GET /api/products/{id}/available-quantity` - Get available quantity

**Shopping Cart**:
- `GET /api/cart` - Get active cart
- `POST /api/cart/add` - Add product to cart
- `PUT /api/cart/products/{cartProductId}` - Update cart item quantity
- `DELETE /api/cart/products/{cartProductId}` - Remove product from cart
- `POST /api/cart/finish` - Finish order

All API endpoints require authentication and CSRF token. See [API_ENDPOINTS.md](API_ENDPOINTS.md) for details.

## Support

For issues and questions, please open an issue on GitHub.

## Authors

- **Bosiljka Kostic** - Initial work

## Acknowledgments

- Laravel Framework
- React
- Inertia.js
- Tailwind CSS
- Radix UI
