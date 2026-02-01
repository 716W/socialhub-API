# SocialHub API

A modern RESTful API for a social media platform built with Laravel. This API provides endpoints for user authentication, post management, comments, and likes functionality.

## 🚀 Project Idea

SocialHub is a backend API for a social media application where users can:

-   Register and authenticate securely with email verification
-   Verify email via web link or mobile OTP (6-digit code)
-   Create, read, update, and delete posts
-   Comment on posts
-   Like/unlike posts
-   View all posts and comments with user information

## 📋 Features

-   **User Authentication**: Secure registration, login, and logout using Laravel Sanctum
-   **Email Verification**: 
    -   Web verification via signed email links
    -   Mobile OTP verification with 6-digit codes
    -   Secure hashed OTP storage (SHA-256)
    -   Attempt limiting (max 5 tries per OTP)
    -   Configurable OTP expiration (default 10 minutes)
    -   Queued email sending for better performance
-   **Post Management**: Full CRUD operations for posts
-   **Comments System**: Nested comments on posts with full CRUD support
-   **Like System**: Toggle like/unlike functionality on posts
-   **API Documentation**: Auto-generated interactive API documentation using Scramble
-   **Authorization**: Users can only modify their own posts and comments
-   **Testing**: Comprehensive test suite using Pest PHP

## 🛠️ Technologies & Tools

-   **Framework**: Laravel 11.x
-   **Authentication**: Laravel Sanctum (Token-based authentication)
-   **API Documentation**: Scramble - Auto-generates OpenAPI/Swagger documentation
-   **Testing**: Pest PHP - Modern testing framework for PHP
-   **Database**: MySQL/PostgreSQL support
-   **Code Quality**: PHPStan for static analysis

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php      # User login
│   │   │   ├── RegisterController.php   # User registration
│   │   │   └── LogoutController.php     # User logout
│   │   ├── VerifyEmailController.php    # Web email verification (signed link)
│   │   ├── ResendVerificationController.php  # Resend verification link/email
│   │   ├── MobileAuthController.php     # Mobile OTP verification & resend
│   │   ├── PostController.php           # Post CRUD operations
│   │   ├── CommentController.php        # Comment CRUD operations
│   │   ├── UserController.php           # User management
│   │   └── LikeController.php           # Like/unlike toggle
│   ├── Requests/                        # Form request validation
│   ├── Resources/                       # API resource transformations
│   └── Traits/
│       └── ApiResponse.php              # Standardized API responses
├── Mail/
│   └── OtpMail.php                      # OTP email (queued)
├── Models/
│   ├── User.php                         # User model (with email verification)
│   ├── Post.php                         # Post model
│   └── Comment.php                      # Comment model
├── Policies/
│   ├── PostPolicy.php                   # Post authorization
│   ├── CommentPolicy.php                # Comment authorization
│   └── UserPolicy.php                   # User authorization
└── Services/
    ├── VerificationService.php          # Email verification & OTP logic
    ├── LoginService.php                 # Login business logic
    ├── RegisterService.php              # Registration business logic
    ├── PostService.php                  # Post business logic
    ├── CommentService.php               # Comment business logic
    ├── UserService.php                  # User business logic
    └── LikeService.php                  # Like business logic

config/
└── otp.php                              # OTP configuration (length, expiration, attempts)

resources/
└── views/
    └── emails/
        └── otp.blade.php                # Professional OTP email template
```

## 🔐 API Endpoints

### Authentication

-   `POST /api/register` - Register new user
-   `POST /api/login` - Login user
-   `POST /api/logout` - Logout user (requires authentication)

### Email Verification

#### Web Verification (Email Link)
-   `GET /api/email/verify/{id}/{hash}` - Verify email via signed link (from email)
-   `POST /api/email/resend` - Resend verification email (requires authentication)
-   `POST /api/email/verification-notification` - Alternative resend endpoint (requires authentication)

#### Mobile Verification (OTP)
-   `POST /api/mobile/verify` - Verify email with 6-digit OTP code (requires authentication)
    -   Body: `{ "code": "123456" }`
-   `POST /api/mobile/resend` - Resend OTP code to email (requires authentication)

### Posts (All require authentication and email verification)

-   `GET /api/posts` - Get all posts
-   `POST /api/posts` - Create new post
-   `GET /api/posts/{id}` - Get specific post
-   `PUT /api/posts/{id}` - Update post (owner only)
-   `DELETE /api/posts/{id}` - Delete post (owner only)

### Comments (All require authentication and email verification)

-   `GET /api/posts/{post}/comments` - Get all comments for a post
-   `POST /api/posts/{post}/comments` - Create comment on post
-   `GET /api/comments/{id}` - Get specific comment
-   `PUT /api/comments/{id}` - Update comment (owner only)
-   `DELETE /api/comments/{id}` - Delete comment (owner only)

### Likes (Requires authentication and email verification)

-   `POST /api/posts/{post}/like` - Toggle like/unlike on post

### Users (Requires authentication and email verification)

-   `GET /api/users` - Get all users
-   `GET /api/users/{id}` - Get specific user
-   `PUT /api/users/{id}` - Update user (owner only)
-   `DELETE /api/users/{id}` - Delete user (owner only)

## 📚 API Documentation with Scramble

This project uses **Scramble** for automatic API documentation generation. Scramble reads your code and generates beautiful, interactive OpenAPI documentation.

### Accessing Documentation

Visit `/docs/api` on your application to view the interactive API documentation.

### Features:

-   **Auto-generated**: Documentation is generated from controllers, requests, and models
-   **Interactive**: Try out endpoints directly from the documentation
-   **Authentication Support**: Built-in bearer token authentication for testing
-   **Type-safe**: Uses PHP types and docblocks for accurate documentation

## 🚦 Getting Started

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   MySQL/PostgreSQL

### Installation

1. Clone the repository

```bash
git clone <repository-url>
cd socialhub
```

2. Install dependencies

```bash
composer install
```

3. Copy environment file

```bash
cp .env.example .env
```

4. Generate application key

```bash
php artisan key:generate
```

5. Configure your database in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=socialhub
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations

```bash
php artisan migrate
```

7. Configure OTP settings (optional) in `.env`

```env
OTP_LENGTH=6              # OTP code length (default: 6)
OTP_EXPIRATION=10         # Minutes until OTP expires (default: 10)
OTP_MAX_ATTEMPTS=5        # Max verification attempts (default: 5)
```

8. Configure mail settings in `.env` for email verification

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@socialhub.com
MAIL_FROM_NAME="${APP_NAME}"
```

9. (Optional) Seed database with test data

```bash
php artisan db:seed
```

10. Start development server

```bash
php artisan serve
```

11. (Optional) Start queue worker for processing OTP emails

```bash
php artisan queue:work
```

### View API Documentation

Navigate to `http://localhost:8000/docs/api` to view the interactive API documentation.

## 🧪 Testing

Run the test suite using Pest:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=PostTest

# Run with coverage
php artisan test --coverage
```

## 🔑 Authentication Flow

### Registration & Email Verification

1. **Register**: Send POST request to `/api/register` with name, email, and password
2. **Receive Token**: Get authentication token in response
3. **Verify Email** (Choose one method):
   
   **Option A: Web Verification (Email Link)**
   - Check your email inbox
   - Click the verification link
   - Email is verified automatically
   
   **Option B: Mobile App (OTP)**
   - Request OTP: System sends 6-digit code to your email
   - Submit code: POST to `/api/mobile/verify` with the code
   - Code valid for 10 minutes with max 5 attempts
   - Request new code if needed via `/api/mobile/resend`

4. **Access Protected Routes**: Use token to access all authenticated endpoints (requires verified email)
5. **Logout**: Send POST to `/api/logout` to revoke token

### Email Verification Security Features

- **Hashed OTP Storage**: OTP codes stored as SHA-256 hashes in database
- **Attempt Limiting**: Maximum 5 verification attempts per OTP code
- **Expiration**: OTP codes expire after 10 minutes
- **Rate Limiting**: Verification endpoints throttled to 6 requests/minute
- **Signed URLs**: Web verification links use Laravel's signed URL feature
- **Queued Emails**: OTP emails processed in background for better performance

## 📝 Example Usage

### Register User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Create Post (with authentication)

```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "content": "My first post!"
  }'
```

### Verify Email with OTP (Mobile)

```bash
# Request OTP code (sent to email)
curl -X POST http://localhost:8000/api/mobile/resend \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Verify with OTP code
curl -X POST http://localhost:8000/api/mobile/verify \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "code": "123456"
  }'
```

## ⚙️ Configuration

### OTP Settings

Configure OTP behavior in `config/otp.php` or via environment variables:

```php
return [
    'length' => env('OTP_LENGTH', 6),           // OTP code length
    'expiration' => env('OTP_EXPIRATION', 10),  // Minutes until expiration
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5), // Maximum verification attempts
];
```

### Queue Configuration

For production, configure a proper queue driver in `.env`:

```env
QUEUE_CONNECTION=redis  # or database, sqs, etc.
```

Run the queue worker:

```bash
php artisan queue:work --tries=3
```

## 🤝 Contributing

Feel free to submit issues and pull requests.

## 📄 License

This project is open-sourced software licensed under the MIT license.

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
