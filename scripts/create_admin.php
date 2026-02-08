<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Repositories\UserRepository;

$email = 'admin@haarlem.nl';
$password = 'Admin123!';

$repo = new UserRepository();

try {
    $repo->createAdmin($email, $password);
    echo "Admin created: {$email} / {$password}\n";
} catch (PDOException $e) {
    // Duplicate email (MariaDB/MySQL error code 1062)
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
        echo "Admin already exists: {$email}\n";
        exit(0);
    }
    throw $e;
}
