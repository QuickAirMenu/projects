<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Services\TeamPayoutService;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

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
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'users' => User::whereIn('id', Task::whereNotNull('assigned_to')->whereNotNull('cost_amount')->distinct()->pluck('assigned_to'))->orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['user_id', 'project_id', 'status']),
        ]);
    }

    public function batchPay(Request $request, TeamPayoutService $service)
    {
        $data = $request->validate([
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'payout_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        $account = Account::findOrFail($data['account_id']);

        $result = $service->batchPay(collect($data['task_ids']), $account, $data['payout_date'], $data['note'] ?? null);

        return back()->with('success', __('reports.payout_batch_done', [
            'tasks' => $result['tasks'],
            'members' => count($result['members']),
        ]));
    }

    public function statementPdf(Request $request, User $user)
    {
        $from = $request->filled('from') ? $request->date('from')->toDateString() : now()->startOfMonth()->toDateString();
        $to = $request->filled('to') ? $request->date('to')->toDateString() : now()->toDateString();

        $tasks = Task::with(['project:id,name', 'paidAccount:id,name'])
            ->where('assigned_to', $user->id)
            ->whereNotNull('cost_paid_at')
            ->whereDate('cost_paid_at', '>=', $from)
            ->whereDate('cost_paid_at', '<=', $to)
            ->orderBy('cost_paid_at')
            ->get();

        $baseTotal = $tasks->sum(fn (Task $task) => (float) $task->cost_amount * (float) ($task->cost_exchange_rate ?: 1));
        $byCurrency = $tasks->groupBy(fn (Task $task) => $task->cost_currency ?: 'SAR')
            ->map(fn ($group) => $group->sum(fn (Task $task) => (float) $task->cost_amount));

        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar', 'company_logo', 'company_email', 'company_phone', 'currency_symbol',
        ])->pluck('value', 'key')->toArray();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'dejavusans', 'autoScriptToLang' => true, 'autoLangToFont' => true]);
        $mpdf->WriteHTML(view('pdf.team-payout-statement', compact('user', 'tasks', 'from', 'to', 'baseTotal', 'byCurrency', 'settings') + [
            'member' => $user,
            'baseCurrency' => app(TreasuryService::class)->baseCurrency(),
        ])->render());

        return response($mpdf->Output('team-statement-'.$user->id.'-'.$from.'_'.$to.'.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="team-statement-'.$user->id.'-'.$from.'_'.$to.'.pdf"',
        ]);
    }
}
