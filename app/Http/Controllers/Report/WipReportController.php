<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Project;
use App\Models\TimeEntry;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WipReportController extends Controller
{
    public function index()
    {
        [$rows, $totals] = $this->rows();

        return view('reports.wip', compact('rows', 'totals'));
    }

    public function export(): StreamedResponse
    {
        [$rows] = $this->rows();
        $filename = 'wip-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Project', 'Client', 'Unbilled hours', 'Labor value', 'Unbilled expenses', 'Total WIP']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['project'],
                    $row['client'],
                    number_format($row['hours'], 2, '.', ''),
                    number_format($row['labor'], 2, '.', ''),
                    number_format($row['expenses'], 2, '.', ''),
                    number_format($row['total'], 2, '.', ''),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function rows(): array
    {
        $unbilledHours = TimeEntry::query()
            ->where('billable', true)
            ->whereNull('invoiced_invoice_id')
            ->selectRaw('project_id, SUM(hours) as hours, SUM(hours * hourly_rate) as value')
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        $unbilledExpenses = Expense::query()
            ->where('status', 'approved')
            ->where('billable', true)
            ->whereNull('invoiced_invoice_id')
            ->whereNotNull('project_id')
            ->selectRaw('project_id, SUM(amount) as total')
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        $ids = $unbilledHours->keys()->merge($unbilledExpenses->keys())->unique()->values();

        $projects = Project::with('client:id,name')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'client_id']);

        $rows = [];
        $totals = ['hours' => 0.0, 'labor' => 0.0, 'expenses' => 0.0, 'total' => 0.0];

        foreach ($projects as $project) {
            $hours = (float) ($unbilledHours[$project->id]->hours ?? 0);
            $labor = (float) ($unbilledHours[$project->id]->value ?? 0);
            $expenses = (float) ($unbilledExpenses[$project->id]->total ?? 0);
            $total = $labor + $expenses;

            if ($total <= 0) {
                continue;
            }

            $rows[] = [
                'id' => $project->id,
                'project' => $project->name,
                'client' => $project->client?->name ?? '-',
                'hours' => $hours,
                'labor' => $labor,
                'expenses' => $expenses,
                'total' => $total,
            ];

            $totals['hours'] += $hours;
            $totals['labor'] += $labor;
            $totals['expenses'] += $expenses;
            $totals['total'] += $total;
        }

        usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [$rows, $totals];
    }
}
