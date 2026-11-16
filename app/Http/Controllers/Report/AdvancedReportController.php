<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdvancedReportController extends Controller
{
    /**
     * Accounts Receivable aging report.
     */
    public function aging(Request $request)
    {
        $rows = $this->agingRows();

        $totals = ['current' => 0.0, 'd1_30' => 0.0, 'd31_60' => 0.0, 'd61_90' => 0.0, 'd90_plus' => 0.0, 'total' => 0.0];
        foreach ($rows as $row) {
            $totals[$row['bucket']] += $row['remaining'];
            $totals['total'] += $row['remaining'];
        }

        return view('reports.aging', compact('rows', 'totals'));
    }

    public function agingExport(): StreamedResponse
    {
        $rows = $this->agingRows();
        $filename = 'ar-aging-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Invoice #', 'Client', 'Due date', 'Days overdue', 'Bucket', 'Remaining', 'Status']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['invoice_number'],
                    $row['client'],
                    $row['due_date'],
                    $row['days_overdue'],
                    $row['bucket_label'],
                    number_format($row['remaining'], 2, '.', ''),
                    $row['payment_status'],
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Project billing summary (invoiced vs collected vs budget).
     */
    public function profitability(Request $request)
    {
        $projects = Project::with(['client:id,name'])
            ->withSum('invoices as invoiced_total', 'total')
            ->withSum('invoices as remaining_total', 'remaining_amount')
            ->orderByDesc('invoiced_total')
            ->paginate(20);

        $collectedByProject = Payment::query()
            ->where('status', 'approved')
            ->whereHas('invoice')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->groupBy('invoices.project_id')
            ->selectRaw('invoices.project_id as project_id, SUM(payments.amount) as collected')
            ->pluck('collected', 'project_id');

        return view('reports.profitability', compact('projects', 'collectedByProject'));
    }

    private function agingRows(): array
    {
        $invoices = Invoice::with('client:id,name')
            ->whereIn('payment_status', ['unpaid', 'partially_paid', 'overdue'])
            ->where('remaining_amount', '>', 0)
            ->orderBy('due_date')
            ->get(['id', 'invoice_number', 'client_id', 'due_date', 'remaining_amount', 'payment_status']);

        $rows = [];
        $today = now()->startOfDay();

        foreach ($invoices as $invoice) {
            $due = $invoice->due_date?->startOfDay();
            $days = $due ? (int) $due->diffInDays($today, false) : 0;

            if ($days <= 0) {
                $bucket = 'current';
                $label = __('reports.aging_current');
            } elseif ($days <= 30) {
                $bucket = 'd1_30';
                $label = __('reports.aging_1_30');
            } elseif ($days <= 60) {
                $bucket = 'd31_60';
                $label = __('reports.aging_31_60');
            } elseif ($days <= 90) {
                $bucket = 'd61_90';
                $label = __('reports.aging_61_90');
            } else {
                $bucket = 'd90_plus';
                $label = __('reports.aging_90_plus');
            }

            $rows[] = [
                'invoice_number' => $invoice->invoice_number,
                'client' => $invoice->client?->name ?? '-',
                'due_date' => $invoice->due_date?->format('Y-m-d') ?? '-',
                'days_overdue' => max(0, $days),
                'bucket' => $bucket,
                'bucket_label' => $label,
                'remaining' => (float) $invoice->remaining_amount,
                'payment_status' => $invoice->payment_status,
            ];
        }

        return $rows;
    }
}
