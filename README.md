# SocialHub API

A modern RESTful API for a social media platform built with Laravel. This API provides endpoints for user authentication, post management, comments, and likes functionality.

## 🚀 Project Idea

SocialHub is a backend API for a social media application where users can:

-   Register and authenticate securely
-   Create, read, update, and delete posts
-   Comment on posts
-   Like/unlike posts
-   View all posts and comments with user information

## 📋 Features

-   **User Authentication**: Secure registration, login, and logout using Laravel Sanctum
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
│   │   ├── PostController.php           # Post CRUD operations
│   │   ├── CommentController.php        # Comment CRUD operations
│   │   └── LikeController.php           # Like/unlike toggle
│   ├── Requests/                        # Form request validation
│   └── Resources/                       # API resource transformations
├── Models/
│   ├── User.php                         # User model
│   ├── Post.php                         # Post model
│   └── Comment.php                      # Comment model
└── Services/
    ├── LoginService.php                 # Login business logic
    ├── RegisterService.php              # Registration business logic
    ├── PostService.php                  # Post business logic
    ├── CommentService.php               # Comment business logic
    └── LikeService.php                  # Like business logic
```

## 🔐 API Endpoints

### Authentication

-   `POST /api/register` - Register new user
-   `POST /api/login` - Login user
-   `POST /api/logout` - Logout user (requires authentication)

### Posts (All require authentication)

-   `GET /api/posts` - Get all posts
-   `POST /api/posts` - Create new post
-   `GET /api/posts/{id}` - Get specific post
-   `PUT /api/posts/{id}` - Update post (owner only)
-   `DELETE /api/posts/{id}` - Delete post (owner only)

### Comments (All require authentication)

-   `GET /api/posts/{post}/comments` - Get all comments for a post
-   `POST /api/posts/{post}/comments` - Create comment on post
-   `GET /api/comments/{id}` - Get specific comment
-   `PUT /api/comments/{id}` - Update comment (owner only)
-   `DELETE /api/comments/{id}` - Delete comment (owner only)

### Likes (Requires authentication)

-   `POST /api/posts/{post}/like` - Toggle like/unlike on post

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

7. (Optional) Seed database with test data

```bash
php artisan db:seed
```

8. Start development server

```bash
php artisan serve
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

1. **Register**: Send POST request to `/api/register` with name, email, and password
2. **Receive Token**: Get authentication token in response
3. **Use Token**: Include token in Authorization header: `Bearer {token}`
4. **Access Protected Routes**: Use token to access all authenticated endpoints
5. **Logout**: Send POST to `/api/logout` to revoke token

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

## 🤝 Contributing

Feel free to submit issues and pull requests.

## 📄 License

This project is open-sourced software licensed under the MIT license.

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
