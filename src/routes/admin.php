<?php
declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EventAdminController;

return [
    ['GET',  '/admin',               [DashboardController::class, 'index']],
    ['GET',  '/admin/events',        [EventAdminController::class, 'index']],
    ['GET',  '/admin/events/new',    [EventAdminController::class, 'create']],
    ['POST', '/admin/events/store',  [EventAdminController::class, 'store']],
    ['GET',  '/admin/events/edit',   [EventAdminController::class, 'edit']],     // ?id=1
    ['POST', '/admin/events/update', [EventAdminController::class, 'update']],   // ?id=1
    ['POST', '/admin/events/delete', [EventAdminController::class, 'delete']],   // ?id=1
];
