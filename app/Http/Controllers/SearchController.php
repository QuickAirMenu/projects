<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([
                'projects' => [],
                'clients' => [],
                'invoices' => [],
                'quotes' => [],
                'tasks' => [],
            ]);
        }

        $projects = $this->searchQuery(
            Project::query(),
            ['name', 'project_number'],
            $q
        )->limit(5)->get(['id', 'name', 'project_number']);

        $clients = $this->searchQuery(
            Client::query(),
            ['name', 'company_name', 'email'],
            $q
        )->limit(5)->get(['id', 'name', 'company_name']);

        $invoices = $this->searchQuery(
            Invoice::query(),
            'invoice_number',
            $q
        )->limit(5)->get(['id', 'invoice_number', 'total']);

        $quotes = $this->searchQuery(
            Quote::query(),
            'quote_number',
            $q
        )->limit(5)->get(['id', 'quote_number', 'total']);

        $tasks = $this->searchQuery(
            Task::query(),
            'title',
            $q
        )->limit(5)->get(['id', 'title']);

        return response()->json([
            'projects' => $projects,
            'clients' => $clients,
            'invoices' => $invoices,
            'quotes' => $quotes,
            'tasks' => $tasks,
        ]);
    }

    private function searchQuery($model, $columns, $query)
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $fulltextCols = is_array($columns) ? implode(',', $columns) : $columns;

            return $model->whereRaw("MATCH({$fulltextCols}) AGAINST(? IN BOOLEAN MODE)", [$query.'*']);
        }

        return $model->where(function ($q) use ($columns, $query) {
            foreach ((array) $columns as $col) {
                $q->orWhere($col, 'like', "%{$query}%");
            }
        });
    }
}
