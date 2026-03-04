<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\ScheduleController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\CartController;

return [
    ['GET',  '/',         [HomeController::class, 'index']],
    ['GET',  '/schedule', [ScheduleController::class, 'index']],

    // Auth
    ['GET',  '/login',    [AuthController::class, 'showLogin']],
    ['POST', '/login',    [AuthController::class, 'login']],
    ['POST', '/logout',   [AuthController::class, 'logout']],
    
    // Registration
    ['GET',  '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],
    
    // Profile
    ['GET', '/profile', [AuthController::class, 'showProfile']],

    // Cart
    ['GET', '/cart', [CartController::class, 'show']],
    ['POST', '/cart/add', [CartController::class, 'add']],
    ['POST', '/cart/update', [CartController::class, 'update']],
    ['POST', '/cart/remove', [CartController::class, 'remove']],
    ['POST', '/cart/clear', [CartController::class, 'clear']],
    ['GET', '/checkout', [CartController::class, 'checkout']],

    ['GET', '/contact', [ContactController::class, 'index']],

];
