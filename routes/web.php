<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\ScheduleController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\CartController;
use App\Controllers\PaymentController;
use App\Controllers\UserOrderController;
use App\Controllers\TicketVerificationController;

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
    ['GET', '/profile/orders', [UserOrderController::class, 'orders']],
    ['GET', '/profile/orders/view', [UserOrderController::class, 'orderDetail']],  // ?id=1
    ['GET', '/profile/orders/download/{id}', [UserOrderController::class, 'downloadInvoice']],

    // Ticket Verification (Day 6)
    ['POST', '/ticket/verify', [TicketVerificationController::class, 'verify']],
    ['POST', '/ticket/mark-used', [TicketVerificationController::class, 'markUsed']],
    ['GET', '/ticket/status', [TicketVerificationController::class, 'getStatus']],

    // Cart
    ['GET', '/cart', [CartController::class, 'show']],
    ['POST', '/cart/add', [CartController::class, 'add']],
    ['POST', '/cart/update', [CartController::class, 'update']],
    ['POST', '/cart/remove', [CartController::class, 'remove']],
    ['POST', '/cart/clear', [CartController::class, 'clear']],
    ['GET', '/checkout', [CartController::class, 'checkout']],

    // Payment (Day 4)
    ['POST', '/payment/checkout', [PaymentController::class, 'checkout']],
    ['GET', '/order/success', [PaymentController::class, 'success']],
    ['GET', '/order/confirmation', [PaymentController::class, 'confirmation']],
    ['GET', '/order/cancel', [PaymentController::class, 'cancel']],

    ['GET', '/contact', [ContactController::class, 'index']],

];
