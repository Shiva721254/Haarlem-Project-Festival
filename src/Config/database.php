<?php
declare(strict_types=1);

namespace App\Config;

final class Database
{
    public const HOST     = 'mysql';
    public const PORT     = 3306;
    public const NAME     = 'developmentdb';
    public const USER     = 'developer';
    public const PASSWORD = 'secret123';
    public const CHARSET  = 'utf8mb4';
}
