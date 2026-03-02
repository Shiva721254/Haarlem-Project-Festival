# Week 1 - Sprint 1: User Authentication Enhancement

## 🎯 Sprint Goal
Enhance the authentication system with registration, validation, and security measures.

**Duration**: 3-4 days  
**Grade Impact**: 15% (part of Complete & Secure requirements)

---

## 📋 What We're Building

### Current State ✅
- Login page exists
- Logout works
- Password verification working

### Missing (To Build This Sprint) ⚠️
- [ ] Users table in database
- [ ] Registration page
- [ ] Registration form validation
- [ ] Password strength requirements
- [ ] Email validation
- [ ] Rate limiting on login
- [ ] Proper session security

---

## 🚀 STEP-BY-STEP Implementation

### STEP 1: Git Setup (15 minutes)

#### 1.1 Open GitHub Desktop
- Launch GitHub Desktop
- Select your Haarlem-Project-Festival repository

#### 1.2 Switch to develop branch
- Click "Current Branch" dropdown at top
- Select `develop`
- Click "Fetch origin" to get latest changes

#### 1.3 Create feature branch
- Click "New Branch" button
- Name: `feature/sprint-1-user-auth`
- Create from: `develop`
- Click "Create Branch"

#### 1.4 Publish branch
- Click "Publish branch" to push to GitHub

✅ **Checkpoint**: You should now be on `feature/sprint-1-user-auth` branch

---

### STEP 2: Database - Create Users Table (20 minutes)

#### 2.1 Update schema.sql

**File**: `database/schema.sql`

Add this at the TOP of the file:

```sql
-- Users table with roles
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('visitor', 'employee', 'admin') DEFAULT 'visitor',
    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add default admin user (password: Admin123!)
INSERT INTO users (email, password_hash, role) VALUES
('admin@haarlemfestival.nl', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQ/ZKwQhWo.bCEEVa0b0YXaAm', 'admin')
ON DUPLICATE KEY UPDATE email=email;
```

#### 2.2 Run the migration

Open terminal in VS Code (Ctrl + `) and run:
```bash
docker exec -i mysql mysql -udeveloper -psecret123 developmentdb < database/schema.sql
```

Or if using MySQL client:
```sql
SOURCE database/schema.sql;
```

✅ **Checkpoint**: Verify users table exists with:
```sql
SHOW TABLES;
DESCRIBE users;
```

---

### STEP 3: Create Validator Class (30 minutes)

#### 3.1 Create Validator.php

**File**: `src/Framework/Validator.php`

```php
<?php
declare(strict_types=1);

namespace App\Framework;

final class Validator
{
    /**
     * Validate email format
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     * Requirements: 8+ chars, uppercase, lowercase, number, special char
     */
    public static function password(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }
    
    /**
     * Validate string length
     */
    public static function length(string $value, int $min, int $max): bool
    {
        $len = mb_strlen($value);
        return $len >= $min && $len <= $max;
    }
    
    /**
     * Validate required field
     */
    public static function required(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return !empty($value);
    }
}
```

✅ **Checkpoint**: File created, no syntax errors

---

### STEP 4: Enhance UserRepository (20 minutes)

#### 4.1 Add registration methods

**File**: `src/Repositories/UserRepository.php`

Add these methods to the class:

```php
    /**
     * Create new visitor account
     */
    public function register(string $email, string $password, string $firstName = '', string $lastName = ''): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $this->exec(
            'INSERT INTO users (email, password_hash, role, first_name, last_name)
             VALUES (:email, :hash, :role, :first_name, :last_name)',
            [
                'email' => $email,
                'hash'  => $hash,
                'role'  => 'visitor',
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        );
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool
    {
        $result = $this->one(
            'SELECT id FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );
        
        return $result !== null;
    }
```

✅ **Checkpoint**: Methods added to UserRepository

---

### STEP 5: Add Registration Controller Methods (30 minutes)

#### 5.1 Add to AuthController

**File**: `src/Controllers/AuthController.php`

Add these methods to the AuthController class:

```php
    /**
     * Show registration form
     */
    public function showRegister(): Response
    {
        $this->ensureSession();
        
        return Response::html(view('auth/register', [
            'success' => Flash::get('success'),
            'error'   => Flash::get('error'),
            'errors'  => Flash::get('errors') ?? [],
        ]));
    }
    
    /**
     * Process registration
     */
    public function register(): Response
    {
        $this->ensureSession();
        
        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            return Response::redirect('/register');
        }
        
        // Get form data
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $lastName = trim((string)($_POST['last_name'] ?? ''));
        
        // Validate
        $errors = [];
        
        // Email validation
        if (!\App\Framework\Validator::required($email)) {
            $errors[] = 'Email is required';
        } elseif (!\App\Framework\Validator::email($email)) {
            $errors[] = 'Invalid email format';
        } else {
            $repo = new UserRepository();
            if ($repo->emailExists($email)) {
                $errors[] = 'This email is already registered';
            }
        }
        
        // Password validation
        if (!\App\Framework\Validator::required($password)) {
            $errors[] = 'Password is required';
        } else {
            $passwordErrors = \App\Framework\Validator::password($password);
            $errors = array_merge($errors, $passwordErrors);
        }
        
        // Password confirmation
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match';
        }
        
        // First name validation
        if (!\App\Framework\Validator::required($firstName)) {
            $errors[] = 'First name is required';
        } elseif (!\App\Framework\Validator::length($firstName, 2, 100)) {
            $errors[] = 'First name must be between 2 and 100 characters';
        }
        
        // Last name validation
        if (!\App\Framework\Validator::required($lastName)) {
            $errors[] = 'Last name is required';
        } elseif (!\App\Framework\Validator::length($lastName, 2, 100)) {
            $errors[] = 'Last name must be between 2 and 100 characters';
        }
        
        // If errors, redirect back
        if (!empty($errors)) {
            Flash::set('errors', $errors);
            Flash::set('error', 'Please correct the errors below');
            return Response::redirect('/register');
        }
        
        // Create user
        try {
            $repo = new UserRepository();
            $userId = $repo->register($email, $password, $firstName, $lastName);
            
            // Auto-login after registration
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => $userId,
                'email' => $email,
                'role'  => 'visitor',
            ];
            
            Flash::set('success', 'Registration successful! Welcome to Haarlem Festival.');
            return Response::redirect('/');
        } catch (\Exception $e) {
            Flash::set('error', 'Registration failed. Please try again.');
            return Response::redirect('/register');
        }
    }
```

✅ **Checkpoint**: Methods added, no syntax errors

---

### STEP 6: Create Registration View (25 minutes)

#### 6.1 Create register.php

**File**: `resources/views/auth/register.php`

```php
<?php
declare(strict_types=1);

use App\Framework\Csrf;

/** @var ?string $success */
/** @var ?string $error */
/** @var array $errors */
?>

<h1>Create Account</h1>

<?php if (!empty($success)): ?>
  <div class="flash flash-success">
    <?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="flash flash-error">
    <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="flash flash-error">
    <ul style="margin: 0; padding-left: 20px;">
      <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/register" style="max-width:500px;">
  <?= Csrf::field() ?>

  <div style="margin-bottom:12px;">
    <label for="first_name">First Name *</label><br>
    <input id="first_name" type="text" name="first_name" required 
           style="width:100%; padding:8px;" 
           value="<?= htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div style="margin-bottom:12px;">
    <label for="last_name">Last Name *</label><br>
    <input id="last_name" type="text" name="last_name" required 
           style="width:100%; padding:8px;"
           value="<?= htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div style="margin-bottom:12px;">
    <label for="email">Email *</label><br>
    <input id="email" type="email" name="email" required 
           style="width:100%; padding:8px;"
           value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div style="margin-bottom:12px;">
    <label for="password">Password *</label><br>
    <input id="password" type="password" name="password" required 
           style="width:100%; padding:8px;">
    <small style="color:#666; display:block; margin-top:4px;">
      Must be 8+ characters with uppercase, lowercase, number, and special character
    </small>
  </div>

  <div style="margin-bottom:12px;">
    <label for="password_confirm">Confirm Password *</label><br>
    <input id="password_confirm" type="password" name="password_confirm" required 
           style="width:100%; padding:8px;">
  </div>

  <button type="submit" style="padding:10px 20px; cursor:pointer;">
    Create Account
  </button>
  
  <p style="margin-top:15px;">
    Already have an account? <a href="/login">Login here</a>
  </p>
</form>
```

✅ **Checkpoint**: View file created

---

### STEP 7: Add Registration Routes (10 minutes)

#### 7.1 Update web.php

**File**: `routes/web.php`

Add these routes after the login routes:

```php
    // Registration
    ['GET',  '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],
```

Full section should look like:
```php
    // Auth
    ['GET',  '/login',    [AuthController::class, 'showLogin']],
    ['POST', '/login',    [AuthController::class, 'login']],
    ['POST', '/logout',   [AuthController::class, 'logout']],
    
    // Registration
    ['GET',  '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],
```

✅ **Checkpoint**: Routes added

---

### STEP 8: Update Navbar (10 minutes)

#### 8.1 Add register link

**File**: `resources/views/layout/navbar.php`

Update to include register link:

```php
<nav class="navbar">
    <a href="/" class="logo">Haarlem Festival</a>

    <ul class="nav-links">
        <li><a href="/">Home</a></li>
        <li><a href="/schedule">Schedule</a></li>
        <li><a href="/contact">Contact</a></li>

        <?php if (\App\Framework\Auth::isLoggedIn()): ?>
            <?php if (\App\Framework\Auth::isAdmin()): ?>
                <li><a href="/admin/events">Admin</a></li>
            <?php endif; ?>
            
            <li>
                <form method="POST" action="/logout" style="display:inline;">
                    <?= \App\Framework\Csrf::field() ?>
                    <button type="submit" style="background:none; border:none; color:inherit; cursor:pointer;">
                        Logout
                    </button>
                </form>
            </li>
        <?php else: ?>
            <li><a href="/login">Login</a></li>
            <li><a href="/register">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
```

✅ **Checkpoint**: Navbar updated with login/logout/register links

---

### STEP 9: Enhance Auth Helper (10 minutes)

#### 9.1 Add isLoggedIn method

**File**: `src/Framework/Auth.php`

Add this method:

```php
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user']['id']);
    }
    
    public static function userId(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }
```

✅ **Checkpoint**: Auth helpers added

---

### STEP 10: Test Your Implementation (15 minutes)

#### 10.1 Start your server
```bash
docker-compose up -d
```

Or if using PHP built-in:
```bash
php -S localhost:8000 -t public
```

#### 10.2 Test Registration
1. Go to `http://localhost:8080/register`
2. Try submitting with weak password → Should show errors
3. Try with existing email → Should show error
4. Try with valid data → Should create account & redirect

**Test Cases**:
```
❌ Password "test" → Should reject (too short)
❌ Password "testtest" → Should reject (no uppercase/number/special)
❌ Password "Test123" → Should reject (no special char)
✅ Password "Test123!" → Should accept
```

#### 10.3 Verify Database
```sql
SELECT * FROM users;
```

Should see your new user!

✅ **Checkpoint**: Registration working, user created in database

---

### STEP 11: Commit Your Changes (20 minutes)

#### 11.1 Open GitHub Desktop

You should see all your changed files listed.

#### 11.2 Review Changes

Click each file to review what you changed.

#### 11.3 Create Commit

**Summary**: `Add user registration with validation`

**Description**:
```
- Create users table schema
- Add Validator class for email/password validation
- Implement registration form and controller
- Add first_name and last_name fields
- Enhance UserRepository with register() method
- Add registration routes
- Update navbar with login/register/logout links
- Add Auth::isLoggedIn() helper

Security features:
- Password strength requirements (8+ chars, mixed case, numbers, special)
- Email format validation
- Email uniqueness check
- CSRF protection
- bcrypt password hashing with cost 12
```

#### 11.4 Commit to branch

Click "Commit to feature/sprint-1-user-auth"

#### 11.5 Push to GitHub

Click "Push origin"

✅ **Checkpoint**: First commit pushed to GitHub!

---

## 🎉 Sprint 1 Day 1 Complete!

### What You Built Today:
- ✅ Users database table
- ✅ Registration form with validation
- ✅ Password strength requirements
- ✅ Email validation
- ✅ Secure password hashing
- ✅ Input sanitization
- ✅ CSRF protection

### Grade Impact:
- **Complete** (+5%): Registration feature
- **Secure** (+10%): Validation, hashing, CSRF
- **Scrum** (+2%): Git workflow, clear commits

---

## 📅 Tomorrow (Day 2): Session Security & Error Handling

We'll add:
- Session timeout (1 hour)
- Secure session cookies
- Rate limiting on login
- Better error messages
- Profile page

**Take a break - you earned it!** 🎉

---

## 🐛 Troubleshooting

### "Table users doesn't exist"
Run the schema.sql file again:
```bash
docker exec -i mysql mysql -udeveloper -psecret123 developmentdb < database/schema.sql
```

### "CSRF token invalid"
Make sure you're using `<?= Csrf::field() ?>` in forms

### "Class Validator not found"
Run `composer dump-autoload`

### Registration redirects to 404
Check routes/web.php has the registration routes

---

## ✅ Self-Check Before Ending Day

- [ ] Users table exists in database
- [ ] Can access /register page
- [ ] Weak passwords rejected
- [ ] Strong passwords accepted
- [ ] Email validation works
- [ ] User created in database
- [ ] Auto-login after registration
- [ ] Changes committed to Git
- [ ] Changes pushed to GitHub

**All checked?** → You're ready for Day 2! 🚀
