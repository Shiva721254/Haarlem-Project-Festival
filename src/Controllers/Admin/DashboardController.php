<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Framework\Response;

final class DashboardController
{
    public function index(): Response
    {
        // Dashboard placeholder
        return Response::html('<h1>Admin Dashboard</h1><p>Welcome to the admin dashboard.</p>');
    }
}
