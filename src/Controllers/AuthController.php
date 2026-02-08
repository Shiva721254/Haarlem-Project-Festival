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

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
