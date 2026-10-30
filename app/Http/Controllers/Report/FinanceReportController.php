<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['project', 'client', 'payments']);

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->from) {
            $query->whereDate('invoice_date', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }

        $invoices = $query->latest()->paginate(20);

        $stats = [
            'total_invoiced' => Invoice::sum('total'),
            'total_paid' => Invoice::sum('paid_amount'),
            'total_unpaid' => Invoice::where('payment_status', 'unpaid')->sum('remaining_amount'),
            'total_overdue' => Invoice::where('payment_status', 'overdue')->sum('remaining_amount'),
            'overdue_count' => Invoice::where('payment_status', 'overdue')->count(),
        ];

        $monthlyData = Invoice::selectRaw('MONTH(invoice_date) as month, YEAR(invoice_date) as year, SUM(total) as total, SUM(paid_amount) as paid')
            ->whereYear('invoice_date', now()->year)
            ->groupByRaw('YEAR(invoice_date), MONTH(invoice_date)')
            ->orderByRaw('YEAR(invoice_date), MONTH(invoice_date)')
            ->get();

        return view('reports.finance', compact('invoices', 'stats', 'monthlyData'));
    }
}
