<?php

namespace App\Http\Controllers;

use App\Imports\ClientsImport;
use App\Imports\ProjectsImport;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function showForm()
    {
        $canImportClients = auth()->user()->can('create', Client::class);
        $canImportProjects = auth()->user()->can('create', Project::class);

        abort_unless($canImportClients || $canImportProjects, 403);

        return view('imports.index', compact('canImportClients', 'canImportProjects'));
    }

    public function importClients(Request $request)
    {
        $this->authorize('create', Client::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        $import = new ClientsImport;
        Excel::import($import, $request->file('file'));

        $errors = collect($import->getFailures());
        $errorCount = $errors->count();
        $successCount = $errorCount > 0
            ? __('common.import_success').' ('.__('common.with_errors').')'
            : __('common.import_success');

        if ($errorCount > 0) {
            return back()->with('warning', __('common.import_completed_with_errors', ['count' => $errorCount]));
        }

        return back()->with('success', __('common.import_success'));
    }

    public function importProjects(Request $request)
    {
        $this->authorize('create', Project::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        $import = new ProjectsImport;
        Excel::import($import, $request->file('file'));

        $errors = collect($import->getFailures());
        $errorCount = $errors->count();

        if ($errorCount > 0) {
            return back()->with('warning', __('common.import_completed_with_errors', ['count' => $errorCount]));
        }

        return back()->with('success', __('common.import_success'));
    }

    public function downloadClientTemplate()
    {
        $this->authorize('create', Client::class);

        return Excel::download(new class implements FromArray, WithHeadings
        {
            public function array(): array
            {
                return [];
            }

            public function headings(): array
            {
                return [
                    'name', 'company_name', 'email', 'phone', 'whatsapp',
                    'city', 'country', 'status', 'industry', 'source', 'notes',
                ];
            }
        }, 'clients_template.xlsx');
    }

    public function downloadProjectTemplate()
    {
        $this->authorize('create', Project::class);

        return Excel::download(new class implements FromArray, WithHeadings
        {
            public function array(): array
            {
                return [];
            }

            public function headings(): array
            {
                return [
                    'name', 'client_email', 'client_name', 'description',
                    'start_date', 'deadline', 'priority', 'budget',
                    'progress_percentage', 'notes',
                ];
            }
        }, 'projects_template.xlsx');
    }
}
