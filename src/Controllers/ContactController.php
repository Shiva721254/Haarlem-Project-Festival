<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Response;

final class ContactController
{
    public function index(): Response
    {
        return Response::html(view('contact/index'));
    }
}
