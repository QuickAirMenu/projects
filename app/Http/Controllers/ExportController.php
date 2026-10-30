<?php

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use App\Exports\ContractsExport;
use App\Exports\InvoicesExport;
use App\Exports\PaymentsExport;
use App\Exports\ProjectsExport;
use App\Exports\QuotesExport;
use App\Exports\TasksExport;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Task;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function projects()
    {
        $this->authorize('viewAny', Project::class);

        return Excel::download(
            new ProjectsExport,
            'projects-'.Carbon::now()->format('Y-m-d-His').'.xlsx'
        );
    }

    public function tasks()
    {
        $this->authorize('viewAny', Task::class);

        return Excel::download(
            new TasksExport,
            'tasks-'.Carbon::now()->format('Y-m-d-His').'.xlsx'
        );
    }

    public function clients()
    {
        $this->authorize('viewAny', Client::class);

        return Excel::download(
            new ClientsExport,
            'clients-'.Carbon::now()->format('Y-m-d-His').'.xlsx'
        );
    }

    public function invoices()
    {
        $this->authorize('viewAny', Invoice::class);

        return Excel::download(
            new InvoicesExport,
            'invoices-'.Carbon::now()->format('Y-m-d-His').'.xlsx'
        );
    }

    public function quotes()
    {
        $this->authorize('viewAny', Quote::class);

        return Excel::download(
            new QuotesExport,
            'quotes-'.Carbon::now()->format('Y-m-d-His').'.xlsx'
        );
    }

    public function payments()
    {
        $this->authorize('viewAny', Payment::class);

        return Excel::download(
            new PaymentsExport,
            'payments-'.Carbon::now()->format('Y-m-d-His').'.xlsx'
        );
    }

    public function contracts()
    {
        $this->authorize('viewAny', Contract::class);

        return Excel::download(
            new ContractsExport,
            'contracts-'.Carbon::now()->format('Y-m-d-His').'.xlsx'
        );
    }
}
