<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Csrf;
use App\Framework\Flash;
use App\Framework\RateLimiter;
use App\Framework\Response;
use App\Framework\SecurityLogger;
use App\Framework\SessionManager;
use App\Framework\Validator;
use App\Repositories\UserRepository;

final class AuthController
{
    private function clientIp(): string
    {
        return (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    public function showLogin(): Response
    {
        SessionManager::start();

        return Response::html(view('auth/login', [
            'success' => Flash::get('success'),
            'error'   => Flash::get('error'),
            'old_email' => Flash::get('old_email'),
        ]));
    }

    public function login(): Response
    {
        SessionManager::start();
        $ip = $this->clientIp();

        if (!Csrf::verifyFromPost()) {
            SecurityLogger::warning('auth.login.csrf_failed', ['ip' => $ip]);
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            return Response::redirect('/login');
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');

        if ($email === '' || $pass === '') {
            SecurityLogger::warning('auth.login.missing_fields', ['ip' => $ip]);
            Flash::set('error', 'Email and password are required.');
            return Response::redirect('/login');
        }
        
        // Check rate limiting
        if (RateLimiter::isLimited($email)) {
            $remainingTime = RateLimiter::getRemainingLockoutTime($email);
            $minutes = ceil($remainingTime / 60);
            SecurityLogger::warning('auth.login.rate_limited', [
                'email' => $email,
                'ip' => $ip,
                'remaining_seconds' => $remainingTime,
            ]);
            Flash::set('error', "Too many failed login attempts. Please try again in {$minutes} minute(s).");
            return Response::redirect('/login');
        }

        $repo = new UserRepository();
        $user = $repo->findByEmail($email);

        if (!$user || !password_verify($pass, (string)$user['password_hash'])) {
            RateLimiter::recordAttempt($email);
            SecurityLogger::warning('auth.login.failed', ['email' => $email, 'ip' => $ip]);
            Flash::set('error', 'Invalid credentials.');
            Flash::set('old_email', $email);
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

        SecurityLogger::info('auth.login.success', [
            'email' => (string)$user['email'],
            'ip' => $ip,
            'role' => (string)$user['role'],
        ]);

        Flash::set('success', 'Welcome back!');
        return Response::redirect('/admin/events');
    }

    public function logout(): Response
    {
        SessionManager::start();
        $ip = $this->clientIp();
        $currentUser = Auth::user();

        if (!Csrf::verifyFromPost()) {
            SecurityLogger::warning('auth.logout.csrf_failed', ['ip' => $ip]);
            Flash::set('error', 'Invalid CSRF token.');
            return Response::redirect('/');
        }

        SessionManager::destroy();

        SecurityLogger::info('auth.logout.success', [
            'email' => (string)($currentUser['email'] ?? 'unknown'),
            'ip' => $ip,
        ]);
        
        Flash::set('success', 'Logged out.');
        return Response::redirect('/');
    }
    
    /**
     * Show registration form
     */
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
    
    public function showRegister(): Response
    {
        SessionManager::start();
        
        return Response::html(view('auth/register', [
            'success' => Flash::get('success'),
            'error'   => Flash::get('error'),
            'errors'  => Flash::get('errors') ?? [],
            'old'     => Flash::get('old') ?? [],
        ]));
    }
    
    /**
     * Process registration
     */
    public function register(): Response
    {
        SessionManager::start();
        $ip = $this->clientIp();
        
        // Get form data first
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $lastName = trim((string)($_POST['last_name'] ?? ''));
        
        if (!Csrf::verifyFromPost()) {
            SecurityLogger::warning('auth.register.csrf_failed', ['ip' => $ip]);
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            Flash::set('old', [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            return Response::redirect('/register');
        }
        
        // Validate
        $errors = [];
        
        // Email validation
        if (!Validator::required($email)) {
            $errors[] = 'Email is required';
        } elseif (!Validator::email($email)) {
            $errors[] = 'Invalid email format';
        } else {
            $repo = new UserRepository();
            if ($repo->emailExists($email)) {
                $errors[] = 'This email is already registered';
            }
        }
        
        // Password validation
        if (!Validator::required($password)) {
            $errors[] = 'Password is required';
        } else {
            $passwordErrors = Validator::password($password);
            $errors = array_merge($errors, $passwordErrors);
        }
        
        // Password confirmation
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match';
        }
        
        // First name validation
        if (!Validator::required($firstName)) {
            $errors[] = 'First name is required';
        } elseif (!Validator::length($firstName, 2, 100)) {
            $errors[] = 'First name must be between 2 and 100 characters';
        }
        
        // Last name validation
        if (!Validator::required($lastName)) {
            $errors[] = 'Last name is required';
        } elseif (!Validator::length($lastName, 2, 100)) {
            $errors[] = 'Last name must be between 2 and 100 characters';
        }
        
        // If errors, redirect back
        if (!empty($errors)) {
            SecurityLogger::warning('auth.register.validation_failed', [
                'email' => $email,
                'ip' => $ip,
                'error_count' => count($errors),
            ]);
            Flash::set('errors', $errors);
            Flash::set('error', 'Please correct the errors below');
            Flash::set('old', [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            return Response::redirect('/register');
        }
        
        // Create user
        try {
            $repo = new UserRepository();
            $userId = $repo->register($email, $password, $firstName, $lastName);
            
            // Auto-login after registration
            SessionManager::regenerate();
            $_SESSION['user'] = [
                'id'    => $userId,
                'email' => $email,
                'role'  => 'visitor',
            ];

            SecurityLogger::info('auth.register.success', ['email' => $email, 'ip' => $ip]);
            
            Flash::set('success', 'Registration successful! Welcome to Haarlem Festival.');
            return Response::redirect('/');
        } catch (\PDOException $e) {
            $sqlState = (string)($e->getCode() ?? '');
            $message = $e->getMessage();
            $isDuplicateEmail = $sqlState === '23000' || str_contains(strtolower($message), 'duplicate entry');

            SecurityLogger::error('auth.register.failed', [
                'email' => $email,
                'ip' => $ip,
                'reason' => $message,
            ]);

            if ($isDuplicateEmail) {
                Flash::set('errors', ['This email is already registered']);
                Flash::set('error', 'Please correct the errors below');
            } else {
                Flash::set('error', 'Registration failed. Please try again.');
            }

            Flash::set('old', [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            return Response::redirect('/register');
        } catch (\Exception $e) {
            SecurityLogger::error('auth.register.failed', [
                'email' => $email,
                'ip' => $ip,
                'reason' => $e->getMessage(),
            ]);
            Flash::set('error', 'Registration failed. Please try again.');
            Flash::set('old', [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            return Response::redirect('/register');
        }
    }

}
