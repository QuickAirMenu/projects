<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index()
    {
        $data = app(DashboardService::class)->getStats();

        return view('dashboard.index', $data);
    }
}
