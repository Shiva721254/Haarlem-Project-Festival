<?php
declare(strict_types=1);

namespace App\Framework;

use App\Config\Database;
use PDO;
use PDOException;

abstract class Repository
{
    private static ?PDO $pdo = null;

    final protected function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            Database::HOST,
            Database::PORT,
            Database::NAME,
            Database::CHARSET
        );

        try {
            self::$pdo = new PDO($dsn, Database::USER, Database::PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Clear message for debugging (container logs / dev)
            throw new PDOException('DB connection failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }

        return self::$pdo;
    }

    /**
     * @param array<string, scalar|null> $params
     * @return array<int, array<string, mixed>>
     */
    final protected function all(string $sql, array $params = []): array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();
        return $rows;
    }

    /**
     * @param array<string, scalar|null> $params
     * @return array<string, mixed>|null
     */
    final protected function one(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @param array<string, scalar|null> $params
     */
    final protected function exec(string $sql, array $params = []): int
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
