<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\ScheduleController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;

return [
    ['GET',  '/',         [HomeController::class, 'index']],
    ['GET',  '/schedule', [ScheduleController::class, 'index']],

    // Auth
    ['GET',  '/login',    [AuthController::class, 'showLogin']],
    ['POST', '/login',    [AuthController::class, 'login']],
    ['POST', '/logout',   [AuthController::class, 'logout']],

    ['GET', '/contact', [ContactController::class, 'index']],

];
