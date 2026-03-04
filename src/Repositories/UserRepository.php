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
        
        return (int)$this->pdo()->lastInsertId();
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
}
