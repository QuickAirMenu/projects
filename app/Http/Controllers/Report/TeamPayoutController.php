<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TeamPayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project:id,name', 'assignedUser:id,name'])
            ->whereNotNull('cost_amount')
            ->where('cost_amount', '>', 0)
            ->when($request->filled('user_id'), fn ($q) => $q->where('assigned_to', $request->integer('user_id')))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->input('status') === 'paid', fn ($q) => $q->whereNotNull('cost_paid_at'))
            ->when($request->input('status') !== 'paid' && $request->input('status') !== 'all', fn ($q) => $q->whereNull('cost_paid_at'));

        $totals = [
            'unpaid' => (float) Task::whereNotNull('cost_amount')->whereNull('cost_paid_at')->selectRaw('SUM(cost_amount * COALESCE(cost_exchange_rate,1)) as total')->value('total'),
            'paid' => (float) Task::whereNotNull('cost_amount')->whereNotNull('cost_paid_at')->selectRaw('SUM(cost_amount * COALESCE(cost_exchange_rate,1)) as total')->value('total'),
            'count' => (clone $query)->count(),
        ];

        $tasks = $query->orderByDesc('cost_amount')->paginate(30)->withQueryString();

        return view('reports.team-payouts', [
            'tasks' => $tasks,
            'totals' => $totals,
            'users' => User::whereIn('id', Task::whereNotNull('assigned_to')->whereNotNull('cost_amount')->distinct()->pluck('assigned_to'))->orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['user_id', 'project_id', 'status']),
        ]);
    }
}
