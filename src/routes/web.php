<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\ScheduleController;

return [
    ['GET', '/',         [HomeController::class, 'index']],
    ['GET', '/schedule', [ScheduleController::class, 'index']],
];
