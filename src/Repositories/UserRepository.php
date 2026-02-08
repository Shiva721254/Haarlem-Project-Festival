<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;

final class UserRepository extends Repository
{
    public function findByEmail(string $email): ?array
    {
        return $this->one(
            'SELECT id, email, password_hash, role
             FROM users
             WHERE email = :email
             LIMIT 1',
            ['email' => $email]
        );
    }

    public function createAdmin(string $email, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->exec(
            'INSERT INTO users (email, password_hash, role)
             VALUES (:email, :hash, :role)',
            [
                'email' => $email,
                'hash'  => $hash,
                'role'  => 'admin',
            ]
        );
    }
}
