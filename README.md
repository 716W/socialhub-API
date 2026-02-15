# SocialHub API

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Sanctum](https://img.shields.io/badge/Sanctum-Auth-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**A Modern Social Networking RESTful API built with Clean Architecture Principles**

*Final Project - Botfi Intensive Training Program*

[Features](#-key-features) • [Installation](#-installation--setup) • [API Documentation](#-api-endpoints) • [Database Schema](#-database-schema)

</div>

---

## 📖 About The Project

**SocialHub API** is a comprehensive backend system for a social networking platform, developed as the capstone project of an intensive Laravel training program at **Botfi Company**. This project demonstrates professional-grade API development using Laravel's best practices, clean architecture patterns, and modern authentication mechanisms.

This project was built under the expert mentorship of **Omar Baflah** ([GitHub](https://github.com/omerbaflah)), implementing industry-standard patterns including Service Layer Architecture, Repository Pattern, Policy-Based Authorization, and Test-Driven Development principles.

---

## ✨ Key Features

### Core Functionality
- **Secure Authentication System** - JWT-based authentication powered by Laravel Sanctum
- **Email Verification** - Mandatory email verification with resend functionality
- **Mobile OTP Verification** - Alternative verification method for mobile applications
- **Role-Based Access Control (RBAC)** - Admin and User roles with granular permissions
- **Social Interactions** - Create posts, comment, like, and engage with content
- **User Profiles** - Customizable profiles with bio, avatar, and website links
- **Advanced Tagging System** - Many-to-Many relationship for flexible content categorization
- **Category Management** - Admin-controlled content categorization
- **Media Management** - Dedicated service for image uploads, replacements, and deletions

### Technical Highlights
- **Service Layer Pattern** - Complete separation of business logic from controllers
- **Database Transactions** - ACID-compliant operations for critical data integrity
- **Policy-Based Authorization** - Laravel Gates and Policies for fine-grained access control
- **API Resources** - Consistent, formatted JSON responses across all endpoints
- **Form Request Validation** - Dedicated validation classes for clean controller logic
- **Dependency Injection** - Loose coupling and high testability through DI containers
- **RESTful Design** - Industry-standard REST API conventions
- **Automatic Documentation** - API documentation via Dedoc Scramble

---

## 🏗️ Architecture Overview

### Why Service Layer Pattern?

This project strictly follows the **Service Layer Pattern** to achieve:

1. **Separation of Concerns**: Controllers handle HTTP requests/responses only, while Services contain all business logic
2. **Reusability**: Services can be injected into multiple controllers, commands, or jobs
3. **Testability**: Business logic can be unit tested independently of HTTP layer
4. **Maintainability**: Changes to business rules don't require controller modifications
5. **Scalability**: Easy to extend functionality without breaking existing code

### Architecture Flow

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐      ┌──────────┐
│   Client    │─────▶│  Controller  │─────▶│  Service    │─────▶│  Model   │
│  (Postman)  │      │ (HTTP Layer) │      │  (Business  │      │  (Data)  │
└─────────────┘      └──────────────┘      │   Logic)    │      └──────────┘
                            │               └─────────────┘            │
                            ▼                      │                   │
                     ┌──────────────┐             │                   │
                     │  Form        │             │                   │
                     │  Request     │             ▼                   │
                     │ (Validation) │      ┌─────────────┐            │
                     └──────────────┘      │  Repository │            │
                            │               │  /Helper    │            │
                            ▼               │  Services   │            │
                     ┌──────────────┐      └─────────────┘            │
                     │  Policy      │◀────────────────────────────────┘
                     │(Authorization)│
                     └──────────────┘
                            │
                            ▼
                     ┌──────────────┐
                     │  Resource    │
                     │  (JSON       │
                     │  Formatting) │
                     └──────────────┘
```

### Key Architectural Components

#### 1. **Controllers** (HTTP Layer)
- Handle HTTP requests and responses only
- Delegate business logic to Services
- Return API Resources for consistent JSON formatting
- Example: `PostController` calls `PostService` methods

#### 2. **Services** (Business Logic Layer)
Implemented Services:
- **PostService** - CRUD operations for posts with media handling
- **CommentService** - Comment management and pagination
- **ProfileService** - User profile updates with avatar uploads
- **MediaService** - File upload, replacement, and deletion (injected as dependency)
- **CategoryService** - Category creation with automatic slug generation
- **TagService** - Tag management and slug generation
- **LikeService** - Toggle like functionality
- **AuthServices** - RegisterService, LoginService, VerificationService

#### 3. **Form Requests** (Validation Layer)
- `registerRequest` - User registration validation
- `PostRequest` - Post creation/update validation
- `UpdateProfileRequest` - Profile update with custom uniqueness rules
- `StoreCategoryRequest` / `StoreTagRequest` - Resource validation

#### 4. **Policies** (Authorization Layer)
- **PostPolicy** - Only owners can update/delete their posts (admin bypass)
- **CommentPolicy** - Owner-based authorization for comments (admin bypass)
- **CategoryPolicy** - Admin-only category creation
- **UserPolicy** - Self-resource management rules

#### 5. **API Resources** (Transformation Layer)
- `PostResource` - Formats posts with author, like count, formatted dates
- `CommentResource` - Formats comments with human-readable timestamps
- `ProfileResource` - Includes avatar URLs and relative time formatting
- `CategoryResource` / `TagResource` - Consistent slug-based responses

---

## 🗄️ Database Schema

### Entity Relationship Diagram

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│    Users     │         │  Categories  │         │     Tags     │
├──────────────┤         ├──────────────┤         ├──────────────┤
│ id (PK)      │         │ id (PK)      │         │ id (PK)      │
│ name         │         │ name         │         │ name         │
│ email        │         │ slug         │         │ slug         │
│ password     │         └──────────────┘         └──────────────┘
│ role         │                │                         │
│ otp_code     │                │                         │
└──────────────┘                │                         │
       │                        │                         │
       │ 1:1                    │                         │
       ▼                        │ 1:N                     │ M:N
┌──────────────┐                │                         │
│UserProfiles  │                │                         │
├──────────────┤                │                         │
│ id (PK)      │                │                         │
│ user_id (FK) │                │                         │
│ username     │                │                         │
│ bio          │                │                         │
│ avatar       │                │                         │
│ website      │                │                         │
└──────────────┘                │                         │
                                │                         │
┌──────────────┐                │                         │
│    Posts     │◀───────────────┘                         │
├──────────────┤                                          │
│ id (PK)      │◀─────────────────────────────────────────┘
│ user_id (FK) │─────┐     (via post_tag pivot table)
│ category_id  │     │
│ content      │     │ 1:N
│ image        │     │
└──────────────┘     │
       │             │
       │ 1:N         │
       ▼             ▼
┌──────────────┐  ┌──────────────┐
│  Comments    │  │    Likes     │
├──────────────┤  ├──────────────┤
│ id (PK)      │  │ id (PK)      │
│ user_id (FK) │  │ user_id (FK) │
│ post_id (FK) │  │ post_id (FK) │
│ content      │  └──────────────┘
└──────────────┘
```

### Relationships Explained

#### One-to-One
- **User ↔ UserProfile** - Each user has exactly one profile

#### One-to-Many
- **User → Posts** - A user can create multiple posts
- **User → Comments** - A user can write multiple comments
- **Category → Posts** - A category contains multiple posts
- **Post → Comments** - A post can have multiple comments

#### Many-to-Many
- **Posts ↔ Tags** - Posts can have multiple tags, tags can belong to multiple posts (via `post_tag` pivot table)
- **Users ↔ Posts (Likes)** - Users can like multiple posts, posts can be liked by multiple users (via `likes` table with unique constraint)

### Key Database Features

- **Foreign Key Constraints** - Cascade deletes for referential integrity
- **Unique Constraints** - Email, username, category/tag slugs
- **Nullable Foreign Keys** - Category is optional for posts (set null on delete)
- **Pivot Tables** - `post_tag` for many-to-many tag relationships
- **Automatic Timestamps** - `created_at` and `updated_at` on all tables
- **OTP System** - OTP code, expiration, and attempt tracking for mobile verification

---

## 🔧 Installation & Setup

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & npm (for frontend/documentation)
- Git

### Step-by-Step Installation

#### 1. Clone the Repository
```bash
git clone https://github.com/YourUsername/socialhub-api.git
cd socialhub-api
```

#### 2. Install Dependencies
```bash
composer install
```

#### 3. Environment Configuration
```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 4. Configure Database
Edit your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=socialhub
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### 5. Configure Email (For Verification)
Add your mail configuration to `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@socialhub.com
MAIL_FROM_NAME="SocialHub"
```

#### 6. Run Migrations
```bash
# Create database tables
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

#### 7. Create Storage Symlink
```bash
# Link public storage for uploaded files
php artisan storage:link
```

#### 8. Start Development Server
```bash
php artisan serve
```

Your API will be available at `http://127.0.0.1:8000`

#### 9. View API Documentation
```bash
# Generate API documentation (Scramble)
php artisan scramble:generate

# Access at: http://127.0.0.1:8000/docs/api
```

---

## 📡 API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/api/register` | Register a new user account | ❌ |
| `POST` | `/api/login` | Login and receive authentication token | ❌ |
| `POST` | `/api/logout` | Logout and revoke current token | ✅ |
| `GET` | `/api/email/verify/{id}/{hash}` | Verify email from link (sent via email) | ❌ |
| `POST` | `/api/email/verification-notification` | Resend email verification link | ✅ |
| `POST` | `/api/mobile/verify` | Verify email using OTP code (mobile) | ✅ |
| `POST` | `/api/mobile/resend` | Resend OTP code for mobile verification | ✅ |

**Authentication Notes:**
- All endpoints except registration and login require `Authorization: Bearer {token}` header
- Most endpoints require email verification (`verified` middleware)
- Tokens are generated using Laravel Sanctum

---

### Post Management

| Method | Endpoint | Description | Authorization |
|--------|----------|-------------|---------------|
| `GET` | `/api/posts` | Get all posts (paginated, 10 per page) | Public |
| `POST` | `/api/posts` | Create a new post | Authenticated + Verified |
| `GET` | `/api/posts/{id}` | Get specific post by ID | Public |
| `PUT` | `/api/posts/{id}` | Update post | Owner only |
| `DELETE` | `/api/posts/{id}` | Delete post | Owner or Admin |
| `POST` | `/api/posts/{id}/like` | Toggle like on post | Authenticated + Verified |

**Post Request Body (Create/Update):**
```json
{
  "content": "Your post content here",
  "category_id": 1,
  "image": "file upload (optional)",
  "tags": [1, 2, 3]
}
```

**Post Response Example:**
```json
{
  "data": {
    "id": 1,
    "post_content": "Amazing Laravel project!",
    "author": {
      "id": 5,
      "name": "John Doe"
    },
    "count_like": 12,
    "posted_at": "2026-02-15"
  }
}
```

---

### Comment Management

| Method | Endpoint | Description | Authorization |
|--------|----------|-------------|---------------|
| `GET` | `/api/posts/{post}/comments` | Get comments for a post | Public |
| `POST` | `/api/posts/{post}/comments` | Create comment on post | Authenticated + Verified |
| `GET` | `/api/comments/{id}` | Get specific comment | Public |
| `PUT` | `/api/comments/{id}` | Update comment | Owner or Admin |
| `DELETE` | `/api/comments/{id}` | Delete comment | Owner or Admin |

**Comment Request Body:**
```json
{
  "content": "Great post!"
}
```

**Comment Response Example:**
```json
{
  "data": {
    "id": 1,
    "content": "Great post!",
    "author": "John Doe",
    "commented_at": "2026-02-15 14:30"
  }
}
```

---

### User & Profile Management

| Method | Endpoint | Description | Authorization |
|--------|----------|-------------|---------------|
| `GET` | `/api/users` | Get all users | Public |
| `GET` | `/api/users/{id}` | Get specific user | Public |
| `PUT` | `/api/users/{id}` | Update user | Owner only |
| `DELETE` | `/api/users/{id}` | Delete user | Owner only |
| `GET` | `/api/user` | Get current authenticated user | Authenticated |
| `GET` | `/api/profile` | Get authenticated user's profile | Authenticated + Verified |
| `POST` | `/api/profile` | Update authenticated user's profile | Authenticated + Verified |

**Profile Update Request Body:**
```json
{
  "username": "johndoe",
  "bio": "Laravel enthusiast and backend developer",
  "website": "https://johndoe.com",
  "avatar": "file upload (jpeg, png, jpg, gif, max 2MB)"
}
```

**Profile Response Example:**
```json
{
  "data": {
    "username": "johndoe",
    "bio": "Laravel enthusiast and backend developer",
    "avatar_url": "http://localhost:8000/storage/avatars/avatar123.jpg",
    "website": "https://johndoe.com",
    "updated_at": "2 hours ago"
  }
}
```

---

### Category Management

| Method | Endpoint | Description | Authorization |
|--------|----------|-------------|---------------|
| `GET` | `/api/categories` | Get all categories | Public |
| `POST` | `/api/categories` | Create new category | **Admin Only** |

**Category Request Body:**
```json
{
  "name": "Technology"
}
```

**Category Response Example:**
```json
{
  "data": {
    "id": 1,
    "name": "Technology",
    "slug": "technology"
  }
}
```

---

### Tag Management

| Method | Endpoint | Description | Authorization |
|--------|----------|-------------|---------------|
| `GET` | `/api/tags` | Get all tags | Public |
| `POST` | `/api/tags` | Create new tag | Authenticated + Verified |

**Tag Request Body:**
```json
{
  "name": "Laravel"
}
```

**Tag Response Example:**
```json
{
  "data": {
    "id": 1,
    "name": "Laravel",
    "slug": "laravel"
  }
}
```

---

## 🧪 Testing

This project uses **Pest PHP** for testing.

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run tests with coverage
php artisan test --coverage
```

---

## 📚 Technologies Used

| Category | Technology |
|----------|------------|
| **Framework** | Laravel 12 |
| **Language** | PHP 8.2+ |
| **Database** | MySQL 8.0 |
| **Authentication** | Laravel Sanctum |
| **Validation** | Form Request Classes |
| **Testing** | Pest PHP |
| **Documentation** | Dedoc Scramble |
| **Architecture** | Service Layer Pattern, Repository Pattern |
| **Design Patterns** | Dependency Injection, Policy Pattern, Resource Pattern |

---

## 👥 Credits & Acknowledgments

This project was developed as part of the **Intensive Backend Development Training Program** at **Botfi Company**.

### Special Thanks

**Instructor**: [Omar Baflah](https://github.com/omerbaflah)
*Senior Backend Developer & Technical Mentor*

Omar's expert guidance in Laravel best practices, clean architecture principles, and professional development workflows made this project possible. His dedication to teaching industry-standard patterns and attention to code quality has been invaluable throughout this learning journey.

**Training Program**: Botfi Company
*For providing a comprehensive, hands-on learning environment*

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

<div align="center">

**Built with ❤️ using Laravel**

*Final Project - Botfi Training Program 2026*

</div>
