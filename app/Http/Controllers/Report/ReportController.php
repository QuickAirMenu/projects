<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;

class ReportController extends Controller
{
    public function index()
    {
        $stats = [
            'projects'  => Project::count(),
            'clients'   => Client::count(),
            'tasks'     => Task::whereNull('parent_task_id')->count(),
            'invoiced'  => Invoice::sum('total'),
            'collected' => Invoice::sum('paid_amount'),
        ];
        return view('reports.index', compact('stats'));
    }
}
