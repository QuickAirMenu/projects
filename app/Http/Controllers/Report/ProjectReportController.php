<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectStatus;
use Illuminate\Http\Request;

class ProjectReportController extends Controller
{
    public function index(Request $request)
    {
        $statuses = ProjectStatus::where('is_active', true)->get();

        $query = Project::with(['client', 'status', 'manager']);

        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $projects = $query->latest()->paginate(20);

        $stats = [
            'total' => Project::count(),
            'in_progress' => Project::whereHas('status', fn ($q) => $q->where('slug', 'in-progress'))->count(),
            'completed' => Project::whereHas('status', fn ($q) => $q->where('slug', 'completed'))->count(),
            'overdue' => Project::where('deadline', '<', now())->whereHas('status', fn ($q) => $q->whereNotIn('slug', ['completed', 'closed', 'cancelled']))->count(),
        ];

        return view('reports.projects', compact('projects', 'statuses', 'stats'));
    }
}
