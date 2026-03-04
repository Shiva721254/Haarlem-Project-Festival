<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Framework\Auth;
use App\Framework\Response;

final class CheckinController
{
    public function index(): Response
    {
        // Verify admin access
        if (!Auth::check() || !Auth::isAdmin()) {
            return Response::redirect('/login');
        }

        return Response::html(view('admin/checkin'));
    }
}
