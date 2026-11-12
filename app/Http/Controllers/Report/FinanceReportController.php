<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $base = $this->filteredQuery($request, $from, $to);

        $invoices = (clone $base)
            ->with(['project', 'client'])
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_invoiced' => (float) (clone $base)->sum('total'),
            'total_paid' => (float) (clone $base)->sum('paid_amount'),
            'total_unpaid' => (float) (clone $base)->whereIn('payment_status', ['unpaid', 'partially_paid'])->sum('remaining_amount'),
            'total_overdue' => (float) (clone $base)->where('payment_status', 'overdue')->sum('remaining_amount'),
            'overdue_count' => (int) (clone $base)->where('payment_status', 'overdue')->count(),
            'invoices_count' => (int) (clone $base)->count(),
        ];

        $monthlyData = $this->monthly(clone $base);
        $topClients = $this->topClients(clone $base);

        return view('reports.finance', compact('invoices', 'stats', 'monthlyData', 'topClients', 'from', 'to'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $invoices = $this->filteredQuery($request, $from, $to)
            ->with(['client'])
            ->orderBy('invoice_date')
            ->get();

        $filename = 'finance-report-'.$from.'_'.$to.'.csv';

        return response()->streamDownload(function () use ($invoices) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens Arabic correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Invoice #', 'Date', 'Client', 'Status', 'Total', 'Paid', 'Remaining', 'Currency']);

            foreach ($invoices as $invoice) {
                fputcsv($out, [
                    $invoice->invoice_number,
                    optional($invoice->invoice_date)->format('Y-m-d'),
                    $invoice->client?->name,
                    $invoice->payment_status,
                    number_format((float) $invoice->total, 2, '.', ''),
                    number_format((float) $invoice->paid_amount, 2, '.', ''),
                    number_format((float) $invoice->remaining_amount, 2, '.', ''),
                    workspace_config('currency.symbol', 'SAR'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $request): array
    {
        $from = $request->filled('from') ? $request->date('from')->toDateString() : now()->startOfYear()->toDateString();
        $to = $request->filled('to') ? $request->date('to')->toDateString() : now()->toDateString();

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    private function filteredQuery(Request $request, string $from, string $to)
    {
        $query = Invoice::query()
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        return $query;
    }

    private function monthly($query)
    {
        $rows = $query->get(['invoice_date', 'total', 'paid_amount']);

        $grouped = [];
        foreach ($rows as $row) {
            if (! $row->invoice_date) {
                continue;
            }
            $key = $row->invoice_date->format('Y-n');
            if (! isset($grouped[$key])) {
                $grouped[$key] = (object) [
                    'year' => (int) $row->invoice_date->format('Y'),
                    'month' => (int) $row->invoice_date->format('n'),
                    'total' => 0.0,
                    'paid' => 0.0,
                ];
            }
            $grouped[$key]->total += (float) $row->total;
            $grouped[$key]->paid += (float) $row->paid_amount;
        }

        ksort($grouped);

        return collect(array_values($grouped));
    }

    private function topClients($query)
    {
        $rows = $query->get(['client_id', 'total', 'paid_amount']);

        $grouped = [];
        foreach ($rows as $row) {
            $id = $row->client_id ?? 0;
            if (! isset($grouped[$id])) {
                $grouped[$id] = (object) ['client_id' => $id, 'total' => 0.0, 'paid' => 0.0, 'count' => 0];
            }
            $grouped[$id]->total += (float) $row->total;
            $grouped[$id]->paid += (float) $row->paid_amount;
            $grouped[$id]->count++;
        }

        $top = collect($grouped)->sortByDesc('total')->take(5)->values();

        $names = Client::whereIn('id', $top->pluck('client_id'))->pluck('name', 'id');

        return $top->map(function ($row) use ($names) {
            $row->name = $names[$row->client_id] ?? '-';

            return $row;
        });
    }
}
