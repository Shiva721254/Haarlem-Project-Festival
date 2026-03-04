<?php
declare(strict_types=1);

namespace App\Framework;

final class SecurityLogger
{
    /**
     * @param array<string, mixed> $context
     */
    public static function info(string $event, array $context = []): void
    {
        self::write('INFO', $event, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(string $event, array $context = []): void
    {
        self::write('WARNING', $event, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $event, array $context = []): void
    {
        self::write('ERROR', $event, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function write(string $level, string $event, array $context = []): void
    {
        $line = sprintf(
            "%s [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $event,
            json_encode(self::sanitizeContext($context), JSON_UNESCAPED_SLASHES)
        );

        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/security.log';
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        error_log(trim($line));
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private static function sanitizeContext(array $context): array
    {
        $sanitized = $context;

        if (isset($sanitized['email'])) {
            $sanitized['email'] = self::maskEmail((string)$sanitized['email']);
        }

        if (isset($sanitized['ip'])) {
            $sanitized['ip'] = (string)$sanitized['ip'];
        }

        if (isset($sanitized['password'])) {
            unset($sanitized['password']);
        }

        return $sanitized;
    }

    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return 'invalid-email';
        }

        [$local, $domain] = $parts;
        if ($local === '') {
            return '*@' . $domain;
        }

        return substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 1)) . '@' . $domain;
    }
}
