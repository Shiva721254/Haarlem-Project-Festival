<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Csrf;
use App\Framework\Flash;
use App\Framework\Response;
use App\Repositories\UserRepository;

final class AuthController
{
    public function showLogin(): Response
    {
        $this->ensureSession();

        return Response::html(view('auth/login', [
            'success' => Flash::get('success'),
            'error'   => Flash::get('error'),
        ]));
    }

    public function login(): Response
    {
        $this->ensureSession();

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

        $repo = new UserRepository();
        $user = $repo->findByEmail($email);

        if (!$user || !password_verify($pass, (string)$user['password_hash'])) {
            Flash::set('error', 'Invalid credentials.');
            return Response::redirect('/login');
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'email' => (string)$user['email'],
            'role'  => (string)$user['role'],
        ];

        Flash::set('success', 'Welcome back!');
        return Response::redirect('/admin/events');
    }

    public function logout(): Response
    {
        $this->ensureSession();

        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token.');
            return Response::redirect('/');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        session_destroy();

        Flash::set('success', 'Logged out.');
        return Response::redirect('/');
    }
    
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

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
