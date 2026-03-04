<?php
declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EventAdminController;
use App\Controllers\Admin\AdminOrderController;
use App\Controllers\Admin\CheckinController;

return [
    ['GET',  '/admin',                 [DashboardController::class, 'index']],

    ['GET',  '/admin/events',          [EventAdminController::class, 'index']],
    ['GET',  '/admin/events/new',      [EventAdminController::class, 'create']],
    ['POST', '/admin/events/store',    [EventAdminController::class, 'store']],

    ['GET',  '/admin/events/edit',     [EventAdminController::class, 'edit']],    // ?id=1
    ['POST', '/admin/events/update',   [EventAdminController::class, 'update']],  // ?id=1
    ['POST', '/admin/events/delete',   [EventAdminController::class, 'delete']],  // ?id=1

    // Orders Management (Day 5)
    ['GET',  '/admin/orders',          [AdminOrderController::class, 'index']],
    ['GET',  '/admin/orders/detail',   [AdminOrderController::class, 'detail']],  // ?id=1
    ['POST', '/admin/orders/status',   [AdminOrderController::class, 'updateStatus']],

    // Ticket Check-in (Day 6 Feature 2)
    ['GET',  '/admin/checkin',         [CheckinController::class, 'index']],
];

