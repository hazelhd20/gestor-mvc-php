<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;

class DashboardController extends Controller
{
    public function index(): void
    {
        Session::start();

        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $this->render('dashboard/index', [
            'user' => $user,
        ]);
    }
}
