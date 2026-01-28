# Controllers Documentation

This document provides detailed information about all controllers in the SocialHub API.

## Table of Contents

- [Authentication Controllers](#authentication-controllers)
- [Email Verification Controllers](#email-verification-controllers)
- [Resource Controllers](#resource-controllers)

---

## Authentication Controllers

### LoginController

**Location:** `app/Http/Controllers/Auth/LoginController.php`

**Purpose:** Handles user login and token generation

**Dependencies:**
- `LoginService` - Business logic for authentication

**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": { "id": 1, "name": "John Doe", "email": "user@example.com" },
    "token": "1|abc123..."
  }
}
```

**Features:**
- Validates credentials using `LoginRequest`
- Generates Sanctum API token
- Returns user data with token

---

### RegisterController

**Location:** `app/Http/Controllers/Auth/RegisterController.php`

**Purpose:** Handles new user registration

**Dependencies:**
- `RegisterService` - Business logic for user creation

**Endpoint:** `POST /api/register`

**Request:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Registration successful",
  "data": {
    "user": { "id": 1, "name": "John Doe", "email": "user@example.com" },
    "token": "1|abc123..."
  }
}
```

**Features:**
- Validates input using `RegisterRequest`
- Creates new user account
- Sends email verification notification
- Returns user data with API token

---

### LogoutController

**Location:** `app/Http/Controllers/Auth/LogoutController.php`

**Purpose:** Handles user logout and token revocation

**Endpoint:** `POST /api/logout`

**Authentication Required:** Yes

**Response:**
```json
{
  "status": "success",
  "message": "Logout successful"
}
```

**Features:**
- Revokes current user's access token
- Requires authentication

---

## Email Verification Controllers

### VerifyEmailController

**Location:** `app/Http/Controllers/VerifyEmailController.php`

**Purpose:** Handles web-based email verification via signed links

**Dependencies:**
- `VerificationService` - Email verification logic

**Endpoint:** `GET /api/email/verify/{id}/{hash}`

**Middleware:**
- `signed` - Validates URL signature
- `throttle:6,1` - Rate limiting (6 requests per minute)

**Parameters:**
- `id` - User ID
- `hash` - SHA1 hash of user email

**Response (Success):**
```json
{
  "status": "success",
  "message": "Email verified successfully"
}
```

**Response (Already Verified):**
```json
{
  "status": "success",
  "message": "Email already verified"
}
```

**Response (Invalid Link):**
```json
{
  "status": "error",
  "message": "Invalid verification link"
}
```

**Features:**
- Validates signed URL
- Checks hash against user email
- Marks email as verified
- Fires `Verified` event
- Prevents double verification

---

### ResendVerificationController

**Location:** `app/Http/Controllers/ResendVerficationController.php`

**Purpose:** Resends email verification link to user

**Dependencies:**
- `VerificationService` - Verification logic

**Endpoints:**
- `POST /api/email/resend`
- `POST /api/email/verification-notification` (Laravel standard)

**Authentication Required:** Yes

**Middleware:**
- `auth:sanctum`
- `throttle:6,1` - Rate limiting

**Response (Link Sent):**
```json
{
  "status": "success",
  "message": "Verification link sent"
}
```

**Response (Already Verified):**
```json
{
  "status": "success",
  "message": "Email already verified"
}
```

**Features:**
- Checks if email already verified
- Sends new verification notification
- Rate limited to prevent abuse

---

### MobileAuthController

**Location:** `app/Http/Controllers/MobileAuthController.php`

**Purpose:** Handles OTP-based email verification for mobile apps

**Dependencies:**
- `VerificationService` - OTP generation and verification

**Authentication Required:** Yes (both endpoints)

---

#### verify()

**Endpoint:** `POST /api/mobile/verify`

**Request:**
```json
{
  "code": "123456"
}
```

**Validation:**
- `code`: required, exactly 6 digits

**Response (Success):**
```json
{
  "status": "success",
  "message": "Email verified successfully."
}
```

**Response (Invalid Code):**
```json
{
  "status": "error",
  "message": "Invalid code"
}
```

**Response (Expired):**
```json
{
  "status": "error",
  "message": "Code expired"
}
```

**Response (Max Attempts):**
```json
{
  "status": "error",
  "message": "Maximum attempts exceeded. Please request a new code."
}
```

**Features:**
- Validates 6-digit OTP code
- Checks expiration (10 minutes default)
- Limits attempts (5 max default)
- Increments attempt counter
- Marks email as verified on success
- Fires `Verified` event

---

#### resend()

**Endpoint:** `POST /api/mobile/resend`

**Response:**
```json
{
  "status": "success",
  "message": "OTP has been resent to your email."
}
```

**Features:**
- Generates new 6-digit OTP
- Resets attempt counter
- Sends OTP via email (queued)
- Updates expiration timestamp

---

## Resource Controllers

### PostController

**Location:** `app/Http/Controllers/PostController.php`

**Purpose:** Full CRUD operations for posts

**Dependencies:**
- `PostService` - Post business logic
- `PostPolicy` - Authorization

**Authentication Required:** Yes (all endpoints)

**Email Verification Required:** Yes

**Endpoints:**
- `GET /api/posts` - List all posts
- `POST /api/posts` - Create new post
- `GET /api/posts/{id}` - Get specific post
- `PUT /api/posts/{id}` - Update post (owner only)
- `DELETE /api/posts/{id}` - Delete post (owner only)

**Features:**
- Pagination support
- Resource transformation
- Policy-based authorization
- Eager loads user and likes data

---

### CommentController

**Location:** `app/Http/Controllers/CommentController.php`

**Purpose:** Full CRUD operations for comments

**Dependencies:**
- `CommentService` - Comment business logic
- `CommentPolicy` - Authorization

**Authentication Required:** Yes (all endpoints)

**Email Verification Required:** Yes

**Endpoints:**
- `GET /api/posts/{post}/comments` - List comments for post
- `POST /api/posts/{post}/comments` - Create comment
- `GET /api/comments/{id}` - Get specific comment
- `PUT /api/comments/{id}` - Update comment (owner only)
- `DELETE /api/comments/{id}` - Delete comment (owner only)

**Features:**
- Nested resource routing
- Resource transformation
- Policy-based authorization
- Eager loads user data

---

### UserController

**Location:** `app/Http/Controllers/UserController.php`

**Purpose:** User management operations

**Dependencies:**
- `UserService` - User business logic
- `UserPolicy` - Authorization

**Authentication Required:** Yes (all endpoints)

**Email Verification Required:** Yes

**Endpoints:**
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get specific user
- `PUT /api/users/{id}` - Update user (owner only)
- `DELETE /api/users/{id}` - Delete user (owner only)

**Features:**
- Pagination support
- Resource transformation
- Policy-based authorization

---

### LikeController

**Location:** `app/Http/Controllers/LikeController.php`

**Purpose:** Toggle like/unlike on posts

**Dependencies:**
- `LikeService` - Like business logic

**Authentication Required:** Yes

**Email Verification Required:** Yes

**Endpoint:** `POST /api/posts/{post}/like`

**Response (Liked):**
```json
{
  "status": "success",
  "message": "Post liked successfully.",
  "data": {
    "liked": true,
    "likes_count": 5
  }
}
```

**Response (Unliked):**
```json
{
  "status": "success",
  "message": "Post unliked successfully.",
  "data": {
    "liked": false,
    "likes_count": 4
  }
}
```

**Features:**
- Toggle functionality (like/unlike)
- Returns updated like count
- Prevents duplicate likes

---

## Common Features

All controllers use:

### ApiResponse Trait

Located in `app/Http/Traits/ApiResponse.php`

**Methods:**
- `successResponse($data, $message, $statusCode)` - Standard success response
- `errorResponse($message, $statusCode)` - Standard error response
- `createdResponse($data, $message)` - 201 Created response
- `paginatedResponse($data, $message)` - Paginated data response

### Middleware

- `auth:sanctum` - Token authentication
- `EnsureEmailIsVerified` - Requires verified email
- `throttle:6,1` - Rate limiting (verification endpoints)
- `signed` - Signed URL validation (web verification)

### Response Format

**Success Response:**
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": { /* response data */ }
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Error message",
  "errors": { /* validation errors */ }
}
```

### Authorization

Controllers use Laravel Policies for authorization:
- `PostPolicy` - Post ownership checks
- `CommentPolicy` - Comment ownership checks
- `UserPolicy` - User ownership checks

**Example:**
```php
$this->authorize('update', $post); // Only owner can update
```
