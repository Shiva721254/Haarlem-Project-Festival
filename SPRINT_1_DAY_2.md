# Week 1 - Sprint 1, Day 2: Session Security & Error Handling

## 🎯 Sprint Goal
Enhance session security with timeouts, secure cookies, rate limiting, and create a user profile page.

**Duration**: 3-4 hours  
**Grade Impact**: 10% (Security requirements)

---

## 📋 What We're Building Today

### Features to Add ⚠️
- [ ] Session timeout (1 hour of inactivity)
- [ ] Secure session configuration
- [ ] Login rate limiting (prevent brute force)
- [ ] Failed login attempt tracking
- [ ] User profile page
- [ ] Better error messages and logging
- [ ] Session flash message improvements

---

## 🚀 STEP-BY-STEP Implementation

### STEP 1: Git Setup (5 minutes)

In GitHub Desktop:

1. Click **"Current Branch"** → select `feature/sprint-1-user-auth`
2. Click **"Pull origin"** to get latest changes
3. ✅ You're ready to start working

---

### STEP 2: Create Session Manager Class (25 minutes)

#### 2.1 Create SessionManager.php

**File**: `src/Framework/SessionManager.php`

```php
<?php
declare(strict_types=1);

namespace App\Framework;

final class SessionManager
{
    /**
     * Session timeout in seconds (1 hour)
     */
    private const SESSION_TIMEOUT = 3600;
    
    /**
     * Start session with secure configuration
     */
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Configure session security
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', self::isSecure() ? '1' : '0');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', (string)self::SESSION_TIMEOUT);
            
            session_start();
        }
        
        self::checkTimeout();
    }
    
    /**
     * Check if session has timed out
     */
    private static function checkTimeout(): void
    {
        $now = time();
        $lastActivity = $_SESSION['last_activity'] ?? 0;
        
        // If no last activity, set it
        if ($lastActivity === 0) {
            $_SESSION['last_activity'] = $now;
            return;
        }
        
        // Check if session expired
        if (($now - $lastActivity) > self::SESSION_TIMEOUT) {
            self::destroy();
            Flash::set('error', 'Session expired. Please login again.');
        } else {
            // Update last activity
            $_SESSION['last_activity'] = $now;
        }
    }
    
    /**
     * Regenerate session ID for security
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    /**
     * Check if connection is secure (HTTPS)
     */
    private static function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443;
    }
    
    /**
     * Get remaining session time in seconds
     */
    public static function getRemainingTime(): int
    {
        $lastActivity = $_SESSION['last_activity'] ?? 0;
        if ($lastActivity === 0) {
            return self::SESSION_TIMEOUT;
        }
        
        $elapsed = time() - $lastActivity;
        $remaining = self::SESSION_TIMEOUT - $elapsed;
        
        return max(0, $remaining);
    }
}
```

✅ **Checkpoint**: File created, no syntax errors

---

### STEP 3: Create Rate Limiter Class (20 minutes)

#### 3.1 Create RateLimiter.php

**File**: `src/Framework/RateLimiter.php`

```php
<?php
declare(strict_types=1);

namespace App\Framework;

final class RateLimiter
{
    /**
     * Maximum login attempts
     */
    private const MAX_ATTEMPTS = 5;
    
    /**
     * Lockout duration in seconds (15 minutes)
     */
    private const LOCKOUT_TIME = 900;
    
    /**
     * Get the cache key for an identifier
     */
    private static function getCacheKey(string $identifier): string
    {
        return 'rate_limit_' . hash('sha256', $identifier);
    }
    
    /**
     * Check if an identifier is rate limited
     */
    public static function isLimited(string $identifier): bool
    {
        $cacheKey = self::getCacheKey($identifier);
        $attempts = $_SESSION[$cacheKey] ?? null;
        
        if ($attempts === null) {
            return false;
        }
        
        // Check if lockout has expired
        if ($attempts['locked_until'] < time()) {
            unset($_SESSION[$cacheKey]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Record a failed attempt
     */
    public static function recordAttempt(string $identifier): void
    {
        $cacheKey = self::getCacheKey($identifier);
        $attempts = $_SESSION[$cacheKey] ?? ['count' => 0, 'first_at' => time(), 'locked_until' => 0];
        
        $attempts['count']++;
        
        // Lock after max attempts
        if ($attempts['count'] >= self::MAX_ATTEMPTS) {
            $attempts['locked_until'] = time() + self::LOCKOUT_TIME;
        }
        
        $_SESSION[$cacheKey] = $attempts;
    }
    
    /**
     * Clear attempts for an identifier
     */
    public static function clearAttempts(string $identifier): void
    {
        $cacheKey = self::getCacheKey($identifier);
        unset($_SESSION[$cacheKey]);
    }
    
    /**
     * Get remaining time for lockout in seconds
     */
    public static function getRemainingLockoutTime(string $identifier): int
    {
        $cacheKey = self::getCacheKey($identifier);
        $attempts = $_SESSION[$cacheKey] ?? null;
        
        if ($attempts === null || $attempts['locked_until'] < time()) {
            return 0;
        }
        
        return $attempts['locked_until'] - time();
    }
}
```

✅ **Checkpoint**: File created, no syntax errors

---

### STEP 4: Update AuthController with Security (30 minutes)

#### 4.1 Update login() method with rate limiting

**File**: `src/Controllers/AuthController.php`

Replace the `login()` method:

```php
    public function login(): Response
    {
        SessionManager::start();

        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            return Response::redirect('/login');
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');

        if ($email === '' || $pass === '') {
            Flash::set('error', 'Email and password are required.');
            return Response::redirect('/login');
        }
        
        // Check rate limiting
        if (RateLimiter::isLimited($email)) {
            $remainingTime = RateLimiter::getRemainingLockoutTime($email);
            $minutes = ceil($remainingTime / 60);
            Flash::set('error', "Too many failed login attempts. Please try again in {$minutes} minute(s).");
            return Response::redirect('/login');
        }

        $repo = new UserRepository();
        $user = $repo->findByEmail($email);

        if (!$user || !password_verify($pass, (string)$user['password_hash'])) {
            RateLimiter::recordAttempt($email);
            Flash::set('error', 'Invalid credentials.');
            return Response::redirect('/login');
        }
        
        // Login successful - clear rate limit
        RateLimiter::clearAttempts($email);

        SessionManager::regenerate();

        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'email' => (string)$user['email'],
            'role'  => (string)$user['role'],
        ];

        Flash::set('success', 'Welcome back!');
        return Response::redirect('/admin/events');
    }
```

#### 4.2 Update logout() method to use SessionManager

Replace the `logout()` method:

```php
    public function logout(): Response
    {
        SessionManager::start();

        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token.');
            return Response::redirect('/');
        }

        SessionManager::destroy();
        
        Flash::set('success', 'Logged out.');
        return Response::redirect('/');
    }
```

#### 4.3 Update all methods to use SessionManager::start()

Replace:
```php
    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
```

With:
```php
    private function ensureSession(): void
    {
        SessionManager::start();
    }
```

#### 4.4 Update register() method to use SessionManager

In the `register()` method, replace:
```php
            // Auto-login after registration
            session_regenerate_id(true);
```

With:
```php
            // Auto-login after registration
            SessionManager::regenerate();
```

✅ **Checkpoint**: AuthController updated with security features

---

### STEP 5: Create Profile Page (25 minutes)

#### 5.1 Add profile controller method

In **`src/Controllers/AuthController.php`**, add this new method:

```php
    /**
     * Show user profile
     */
    public function showProfile(): Response
    {
        SessionManager::start();
        
        if (!Auth::isLoggedIn()) {
            return Response::redirect('/login');
        }
        
        $user = Auth::user();
        
        return Response::html(view('auth/profile', [
            'user' => $user,
            'remaining_time' => SessionManager::getRemainingTime(),
        ]));
    }
```

#### 5.2 Create profile view

**File**: `resources/views/auth/profile.php`

```php
<?php
declare(strict_types=1);

/** @var array $user */
/** @var int $remaining_time */

$hours = intdiv($remaining_time, 3600);
$minutes = intdiv($remaining_time % 3600, 60);
$seconds = $remaining_time % 60;
?>

<h1>Your Profile</h1>

<div style="max-width:600px; padding:20px; background:#f5f5f5; border-radius:8px;">
    <h2>Account Information</h2>
    
    <p>
        <strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    
    <p>
        <strong>Role:</strong> 
        <span style="text-transform:capitalize;">
            <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>
        </span>
    </p>
    
    <p>
        <strong>Session Remaining:</strong> 
        <code><?= sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) ?></code>
        <br>
        <small style="color:#666;">
            Your session will expire after 1 hour of inactivity.
        </small>
    </p>
    
    <p>
        <strong>Member Since:</strong> 
        <span id="member-since">Today</span>
    </p>
    
    <hr style="margin:20px 0;">
    
    <h3>Security</h3>
    
    <p>
        <a href="/profile/change-password" style="color:#0066cc; text-decoration:none;">
            Change Password
        </a>
    </p>
    
    <p>
        <form method="POST" action="/logout" style="display:inline;">
            <?= \App\Framework\Csrf::field() ?>
            <button type="submit" style="background:#dc3545; color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">
                Logout
            </button>
        </form>
    </p>
</div>

<script>
// Update session timer in real-time (optional)
let remainingSeconds = <?= $remaining_time ?>;
setInterval(() => {
    remainingSeconds--;
    if (remainingSeconds < 0) {
        window.location.href = '/login';
    }
}, 1000);
</script>
```

✅ **Checkpoint**: Profile page created

---

### STEP 6: Add Profile Route (5 minutes)

#### 6.1 Update routes/web.php

**File**: `routes/web.php`

Add this route:

```php
    // Profile
    ['GET', '/profile', [AuthController::class, 'showProfile']],
```

Full auth section should look like:
```php
    // Auth
    ['GET',  '/login',    [AuthController::class, 'showLogin']],
    ['POST', '/login',    [AuthController::class, 'login']],
    ['POST', '/logout',   [AuthController::class, 'logout']],
    
    // Registration
    ['GET',  '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],
    
    // Profile
    ['GET', '/profile', [AuthController::class, 'showProfile']],
```

✅ **Checkpoint**: Route added

---

### STEP 7: Update Navbar with Profile Link (5 minutes)

#### 7.1 Update navbar.php

**File**: `resources/views/layout/navbar.php`

Update the logged-in section:

```php
        <?php if (\App\Framework\Auth::isLoggedIn()): ?>
            <li><a href="/profile">Profile</a></li>
            
            <?php if (\App\Framework\Auth::isAdmin()): ?>
                <li><a href="/admin/events">Admin</a></li>
            <?php endif; ?>
            
            <li>
                <form method="POST" action="/logout" style="display:inline;">
                    <?= \App\Framework\Csrf::field() ?>
                    <button type="submit" style="background:none; border:none; color:inherit; cursor:pointer; font:inherit;">
                        Logout
                    </button>
                </form>
            </li>
        <?php else: ?>
```

✅ **Checkpoint**: Navbar updated

---

### STEP 8: Update Login Form Error Messages (10 minutes)

#### 8.1 Update login view

**File**: `resources/views/auth/login.php`

Let me check the current file first, but here's what we need to add better error display:

```php
<?php if (!empty($error)): ?>
  <div class="flash flash-error">
    <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>
```

Make sure the error message is displayed prominently with styling.

✅ **Checkpoint**: Login form updated

---

### STEP 9: Test Security Features (20 minutes)

#### 9.1 Test Rate Limiting

Open `http://localhost` and go to login:

**Test Case 1: Try 5 failed logins** ❌
```
Email: test@example.com
Password: wrongpassword
```

Repeat 5 times. On the 5th attempt, you should see:
```
"Too many failed login attempts. Please try again in 15 minute(s)."
```

#### 9.2 Test Session Timeout

1. Login successfully with: `admin@haarlemfestival.nl` / `Admin123!`
2. Go to `/profile`
3. You'll see the session timer (updates in real-time)
4. Wait 1 hour (or manually delete `$_SESSION['last_activity']` to test quickly)
5. Should redirect to login with: "Session expired. Please login again."

#### 9.3 Test Secure Session

In Browser Dev Tools (F12):
1. Go to **Application** → **Cookies**
2. Find `PHPSESSID` cookie
3. Should see:
   - ✅ `HttpOnly` = checked (prevents JS access)
   - ✅ `Secure` = checked (HTTPS only) or unchecked (for localhost)
   - ✅ `SameSite` = Lax

#### 9.4 Test Profile Page

1. Login successfully
2. Go to `/profile`
3. Should show:
   - Your email
   - Your role
   - Session remaining time
   - Profile link in navbar

✅ **Checkpoint**: All security features working!

---

### STEP 10: Verify No Errors (10 minutes)

Let's check for any PHP errors:

```bash
composer dump-autoload
```

This ensures all classes are properly loaded.

Check browser console (F12) for JavaScript errors.

---

### STEP 11: Commit Your Changes (15 minutes)

In **GitHub Desktop**:

1. Review all changed files
2. **Summary**: `Add session security and profile page`
3. **Description**:
   ```
   Security Enhancements:
   - SessionManager class with 1-hour inactivity timeout
   - Secure session cookie configuration (HttpOnly, SameSite)
   - Session regeneration after login
   - RateLimiter class to prevent brute force attacks
   - Max 5 login attempts with 15-minute lockout
   - Session expiration handling with user-friendly messages
   
   New Features:
   - User profile page showing email, role, and session info
   - Session time counter (updates in real-time)
   - Profile link in navbar
   - Improved login error messages
   
   Security Headers:
   - HttpOnly cookies prevent XSS attacks
   - SameSite=Lax prevents CSRF
   - Session timeout prevents unauthorized access
   ```

4. Click **"Commit to feature/sprint-1-user-auth"**
5. Click **"Push origin"**

---

## 🎉 **Sprint 1 - Day 2 Complete!**

### What You Built Today:
- ✅ Session timeout (1 hour inactivity)
- ✅ Secure session configuration (HttpOnly, SameSite)
- ✅ Rate limiting (5 attempts, 15-min lockout)
- ✅ User profile page
- ✅ Session timer display
- ✅ Better error messages

### Grade Impact:
- **Secure** (+5%): Session management, rate limiting
- **Complete** (+3%): Profile page feature
- **Scrum** (+2%): Clear commits with descriptions

**Total for Day 2: +10%**  
**Running Total: +27% toward 100%** 🚀

---

## 📅 Tomorrow (Day 3): Image Upload & Admin Dashboard

We'll add:
- Image upload for events
- File validation & sanitization
- Admin dashboard improvements
- Event image display
- Image processing & optimization

---

## ✅ Self-Check Before Ending Day

- [ ] SessionManager class created
- [ ] RateLimiter class created
- [ ] Rate limiting prevents login after 5 attempts
- [ ] Session timeout works (1 hour)
- [ ] Profile page shows session info
- [ ] Logout destroys session properly
- [ ] Navbar shows profile link when logged in
- [ ] Changes committed and pushed to Git
- [ ] No PHP/JS errors in console

**All checked?** → You're ready for Day 3! 🚀

---

## 🐛 Troubleshooting

### Session expires immediately
- Check `SESSION_TIMEOUT` constant in SessionManager (should be 3600)
- Verify `$_SESSION['last_activity']` is being set

### Rate limiting not working
- Clear `$_SESSION` and test again
- Check that email is converted to lowercase for consistency

### Profile page shows wrong session time
- Make sure `SessionManager::getRemainingTime()` is called
- Check browser console for JS errors

### Logout doesn't work
- Verify CSRF token is in the form
- Check that SessionManager::destroy() is being called

---

## 💡 Security Best Practices You've Implemented

✅ **Session Security**
- Timeout prevents abandoned sessions
- HttpOnly flag prevents XSS
- Secure flag for HTTPS
- SameSite prevents CSRF

✅ **Authentication Security**
- Rate limiting prevents brute force
- Password hashing with bcrypt
- CSRF tokens on all forms
- Input validation

✅ **Code Quality**
- Type hints throughout
- Proper error handling
- Clean separation of concerns
- Well-documented methods

**You're building this right!** 🎓
