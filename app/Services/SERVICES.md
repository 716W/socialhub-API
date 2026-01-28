# Services Documentation

This document provides detailed information about all services in the SocialHub API. Services contain the business logic layer, keeping controllers thin and focused.

## Table of Contents

- [Authentication Services](#authentication-services)
- [Verification Services](#verification-services)
- [Resource Services](#resource-services)

---

## Authentication Services

### LoginService

**Location:** `app/Services/LoginService.php`

**Purpose:** Handles user authentication and token generation

**Dependencies:**
- `User` model
- Laravel Sanctum

---

#### login(array $credentials): array

Authenticates user and generates API token.

**Parameters:**
- `$credentials` - Array with email and password

**Returns:**
```php
[
    'user' => User,
    'token' => string
]
```

**Process:**
1. Validates credentials using `Auth::attempt()`
2. Retrieves authenticated user
3. Generates Sanctum API token
4. Returns user object with token

**Exceptions:**
- Throws exception if credentials invalid

**Usage:**
```php
$data = $loginService->login([
    'email' => 'user@example.com',
    'password' => 'password123'
]);
```

---

### RegisterService

**Location:** `app/Services/RegisterService.php`

**Purpose:** Handles new user registration

**Dependencies:**
- `User` model
- Laravel Sanctum

---

#### register(array $data): array

Creates new user account and generates token.

**Parameters:**
- `$data` - Array with name, email, password

**Returns:**
```php
[
    'user' => User,
    'token' => string
]
```

**Process:**
1. Creates new user with hashed password
2. Sends email verification notification
3. Generates Sanctum API token
4. Returns user object with token

**Usage:**
```php
$result = $registerService->register([
    'name' => 'John Doe',
    'email' => 'user@example.com',
    'password' => 'password123'
]);
```

**Note:** Password is automatically hashed via User model casting.

---

## Verification Services

### VerificationService

**Location:** `app/Services/VerificationService.php`

**Purpose:** Handles all email verification logic (web links and mobile OTP)

**Dependencies:**
- `User` model
- `OtpMail` mailable
- Carbon (date/time)
- Laravel Events (`Verified`)
- Mail facade

**Configuration:**
Uses `config/otp.php` for OTP settings:
- `otp.length` - OTP code length (default: 6)
- `otp.expiration` - Minutes until expiration (default: 10)
- `otp.max_attempts` - Maximum verification attempts (default: 5)

---

#### verifyEmail(User $user): bool

Verifies user's email for web-based verification.

**Parameters:**
- `$user` - User instance to verify

**Returns:**
- `true` if verification successful
- `false` if already verified or failed

**Process:**
1. Checks if email already verified
2. Marks email as verified using `markEmailAsVerified()`
3. Fires `Verified` event
4. Returns success status

**Usage:**
```php
$verified = $verificationService->verifyEmail($user);

if ($verified) {
    // Email verified successfully
} else {
    // Already verified or error
}
```

---

#### resendLink(User $user): bool

Resends email verification link.

**Parameters:**
- `$user` - User instance

**Returns:**
- `true` if link sent
- `false` if email already verified

**Process:**
1. Checks if email already verified
2. Sends verification notification
3. Returns status

**Usage:**
```php
$sent = $verificationService->resendLink($user);

if (!$sent) {
    // Email already verified
}
```

---

#### sendOtp(User $user): void

Generates and sends OTP code for mobile verification.

**Parameters:**
- `$user` - User instance

**Returns:** void

**Process:**
1. Generates OTP code based on configured length
2. Hashes OTP using SHA-256 for secure storage
3. Updates user record with:
   - `otp_code` - Hashed OTP
   - `otp_expires_at` - Expiration timestamp
   - `otp_attempts` - Reset to 0
4. Queues email with plain OTP code

**Security Features:**
- OTP stored as SHA-256 hash (never stored in plain text)
- Configurable expiration time
- Attempt counter reset on new OTP

**Usage:**
```php
$verificationService->sendOtp($user);
// OTP email queued for delivery
```

**Database Update:**
```php
[
    'otp_code' => hash('sha256', '123456'),
    'otp_expires_at' => Carbon::now()->addMinutes(10),
    'otp_attempts' => 0
]
```

---

#### verifyOtp(User $user, string $inputCode): bool|string

Verifies OTP code submitted by user.

**Parameters:**
- `$user` - User instance
- `$inputCode` - OTP code entered by user

**Returns:**
- `true` - Verification successful
- `string` - Error message if failed:
  - `"Code expired"` - OTP expired
  - `"Maximum attempts exceeded. Please request a new code."` - Too many attempts
  - `"Invalid code"` - Code doesn't match

**Process:**
1. Checks if OTP expired (compares with `otp_expires_at`)
2. Checks if max attempts exceeded
3. Increments attempt counter
4. Compares hashed input with stored hash (timing-safe)
5. If valid:
   - Marks email as verified (if not already)
   - Fires `Verified` event
   - Clears OTP fields
6. Returns result

**Security Features:**
- Expiration check performed first
- Attempt limiting prevents brute force
- Timing-safe hash comparison using `hash_equals()`
- Hashed comparison (never compares plain text)
- Automatic attempt incrementing

**Usage:**
```php
$result = $verificationService->verifyOtp($user, '123456');

if ($result === true) {
    // Email verified successfully
} else {
    // $result contains error message
    return response()->json(['error' => $result], 400);
}
```

**Database Updates on Success:**
```php
[
    'otp_code' => null,
    'otp_expires_at' => null,
    'otp_attempts' => 0,
    'email_verified_at' => Carbon::now() // via markEmailAsVerified()
]
```

**Attempt Limiting Example:**
```php
// User enters wrong code 5 times
// Attempt 1: otp_attempts = 1
// Attempt 2: otp_attempts = 2
// Attempt 3: otp_attempts = 3
// Attempt 4: otp_attempts = 4
// Attempt 5: otp_attempts = 5
// Attempt 6: Returns "Maximum attempts exceeded..."
```

---

## Resource Services

### PostService

**Location:** `app/Services/PostService.php`

**Purpose:** Handles post-related business logic

**Dependencies:**
- `Post` model

---

#### getAllPosts()

Retrieves all posts with pagination and relationships.

**Returns:** Paginated collection of posts

**Process:**
- Eager loads user and likes
- Paginates results
- Orders by creation date (newest first)

**Usage:**
```php
$posts = $postService->getAllPosts();
```

---

#### createPost(array $data, User $user): Post

Creates new post.

**Parameters:**
- `$data` - Post data (content, etc.)
- `$user` - Authenticated user

**Returns:** Created Post instance

**Process:**
1. Associates post with user
2. Creates post record
3. Returns post with relationships

---

#### updatePost(Post $post, array $data): Post

Updates existing post.

**Parameters:**
- `$post` - Post instance to update
- `$data` - Updated data

**Returns:** Updated Post instance

**Note:** Authorization checked in controller via Policy

---

#### deletePost(Post $post): bool

Deletes post.

**Parameters:**
- `$post` - Post instance to delete

**Returns:** Success status

---

### CommentService

**Location:** `app/Services/CommentService.php`

**Purpose:** Handles comment-related business logic

**Dependencies:**
- `Comment` model
- `Post` model

---

#### getPostComments(Post $post)

Retrieves all comments for a post.

**Parameters:**
- `$post` - Post instance

**Returns:** Paginated comments collection

**Process:**
- Eager loads user
- Paginates results
- Orders by creation date

---

#### createComment(array $data, Post $post, User $user): Comment

Creates new comment on post.

**Parameters:**
- `$data` - Comment data
- `$post` - Post being commented on
- `$user` - Authenticated user

**Returns:** Created Comment instance

---

#### updateComment(Comment $comment, array $data): Comment

Updates existing comment.

**Parameters:**
- `$comment` - Comment instance
- `$data` - Updated data

**Returns:** Updated Comment instance

---

#### deleteComment(Comment $comment): bool

Deletes comment.

**Parameters:**
- `$comment` - Comment instance

**Returns:** Success status

---

### UserService

**Location:** `app/Services/UserService.php`

**Purpose:** Handles user management business logic

**Dependencies:**
- `User` model

---

#### getAllUsers()

Retrieves all users with pagination.

**Returns:** Paginated users collection

---

#### getUserById(int $id): User

Retrieves specific user.

**Parameters:**
- `$id` - User ID

**Returns:** User instance

**Exceptions:**
- Throws `ModelNotFoundException` if not found

---

#### updateUser(User $user, array $data): User

Updates user profile.

**Parameters:**
- `$user` - User instance
- `$data` - Updated data

**Returns:** Updated User instance

**Note:** Password automatically hashed if updated

---

#### deleteUser(User $user): bool

Deletes user account.

**Parameters:**
- `$user` - User instance

**Returns:** Success status

---

### LikeService

**Location:** `app/Services/LikeService.php`

**Purpose:** Handles post like/unlike logic

**Dependencies:**
- `Post` model
- `User` model

---

#### toggleLike(Post $post, User $user): array

Toggles like status on post.

**Parameters:**
- `$post` - Post instance
- `$user` - Authenticated user

**Returns:**
```php
[
    'liked' => bool,      // true if liked, false if unliked
    'likes_count' => int  // Total likes on post
]
```

**Process:**
1. Checks if user already liked post
2. If liked: Remove like (unlike)
3. If not liked: Add like
4. Returns new status and count

**Usage:**
```php
$result = $likeService->toggleLike($post, $user);

if ($result['liked']) {
    // User liked the post
} else {
    // User unliked the post
}
```

---

## Service Design Patterns

### Constructor Dependency Injection

All services use constructor injection for dependencies:

```php
public function __construct(
    protected PostService $postService
) {}
```

### Return Types

Services use explicit return types for better type safety:

```php
public function createPost(array $data, User $user): Post
{
    // Implementation
}
```

### Error Handling

Services throw exceptions for errors; controllers handle them:

```php
// Service
public function getUserById(int $id): User
{
    return User::findOrFail($id); // Throws ModelNotFoundException
}

// Controller catches and returns appropriate response
```

### Business Logic Separation

Services contain all business logic, keeping controllers thin:

- ✅ Services: Data manipulation, validation logic, complex queries
- ✅ Controllers: HTTP handling, authorization, response formatting

### Configuration Usage

Services use config files instead of hardcoded values:

```php
// ✅ Good
$length = config('otp.length', 6);

// ❌ Bad
$length = 6;
```

### Security Best Practices

1. **Hashing sensitive data:**
   ```php
   'otp_code' => hash('sha256', $code)
   ```

2. **Timing-safe comparisons:**
   ```php
   hash_equals($user->otp_code, hash('sha256', $inputCode))
   ```

3. **Attempt limiting:**
   ```php
   if ($user->otp_attempts >= config('otp.max_attempts', 5)) {
       return 'Maximum attempts exceeded';
   }
   ```

4. **Expiration checks:**
   ```php
   if (Carbon::now()->greaterThan($user->otp_expires_at)) {
       return 'Code expired';
   }
   ```

---

## Testing Services

Services should be unit tested independently:

```php
it('generates and sends OTP code', function () {
    $user = User::factory()->create();
    
    Mail::fake();
    
    $this->verificationService->sendOtp($user);
    
    Mail::assertSent(OtpMail::class);
    expect($user->fresh()->otp_code)->not->toBeNull();
    expect($user->fresh()->otp_attempts)->toBe(0);
});
```
