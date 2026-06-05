<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::with(['project','client','creator'])->latest()->paginate(15);
        $stats = [
            'active_value' => Contract::where('status', 'active')->sum('contract_value'),
            'ended_count' => Contract::whereIn('status', ['completed', 'terminated'])->count(),
            'signed_count' => Contract::where('signed_by_client', true)->count(),
        ];

        return view('finance.contracts.index', compact('contracts', 'stats'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $clients  = Client::orderBy('name')->get();
        $prefillData = null;

        if (request()->filled('invoice_id')) {
            $invoice = Invoice::with(['client', 'project', 'items'])->findOrFail(request('invoice_id'));
            $prefillData = [
                'invoice' => $invoice,
                'project_id' => request('project_id', $invoice->project_id),
                'client_id' => request('client_id', $invoice->client_id),
                'title' => 'عقد مرتبط بالفاتورة ' . $invoice->invoice_number,
                'contract_value' => request('contract_value', $invoice->total),
            ];
        }

        return view('finance.contracts.create', compact('projects','clients','prefillData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'client_id'      => 'required|exists:clients,id',
            'invoice_id'     => 'nullable|exists:invoices,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'contract_value' => 'required|numeric|min:0',
            'status'         => 'required|in:draft,active,completed,terminated',
            'payment_terms'  => 'nullable|string',
            'deliverables'   => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'signed_by_client' => 'nullable|boolean',
            'signed_at'      => 'nullable|date|required_if:signed_by_client,1',
            'contract_file'  => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $request->only([
            'project_id', 'client_id', 'invoice_id', 'title', 'description', 'deliverables',
            'terms_and_conditions', 'start_date', 'end_date', 'contract_value', 'payment_terms',
            'status', 'signed_at',
        ]);
        $data['signed_by_client'] = $request->boolean('signed_by_client');
        $data['signed_at'] = $data['signed_by_client'] ? ($data['signed_at'] ?? now()) : null;
        $data['created_by']      = Auth::id();
        $data['contract_number'] = 'CNT-'.date('Ym').'-'.str_pad(Contract::count()+1,4,'0',STR_PAD_LEFT);

        if ($request->hasFile('contract_file')) {
            $data['contract_file_path'] = $request->file('contract_file')->store('contracts','public');
        }

        Contract::create($data);
        return redirect()->route('contracts.index')
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Contract $contract)
    {
        $contract->load(['project.invoices', 'client', 'creator', 'createdBy', 'invoice']);
        $projectInvoices = $contract->project ? $contract->project->invoices()->with('payments')->get() : collect();

        return view('finance.contracts.show', compact('contract', 'projectInvoices'));
    }

    public function edit(Contract $contract)
    {
        $projects = Project::orderBy('name')->get();
        $clients  = Client::orderBy('name')->get();
        return view('finance.contracts.create', compact('contract','projects','clients'));
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'client_id'      => 'required|exists:clients,id',
            'invoice_id'     => 'nullable|exists:invoices,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'contract_value' => 'required|numeric|min:0',
            'status'         => 'required|in:draft,active,completed,terminated',
            'payment_terms'  => 'nullable|string',
            'deliverables'   => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'signed_by_client' => 'nullable|boolean',
            'signed_at'      => 'nullable|date|required_if:signed_by_client,1',
            'contract_file'  => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
        $data = $request->only([
            'project_id', 'client_id', 'invoice_id', 'title', 'description', 'deliverables',
            'terms_and_conditions', 'start_date', 'end_date', 'contract_value', 'payment_terms',
            'status', 'signed_at',
        ]);
        $data['signed_by_client'] = $request->boolean('signed_by_client');
        $data['signed_at'] = $data['signed_by_client'] ? ($data['signed_at'] ?? $contract->signed_at ?? now()) : null;
        if ($request->hasFile('contract_file')) {
            if ($contract->contract_file_path) Storage::disk('public')->delete($contract->contract_file_path);
            $data['contract_file_path'] = $request->file('contract_file')->store('contracts','public');
        }
        $contract->update($data);
        return redirect()->route('contracts.show', $contract)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Contract $contract)
    {
        if ($contract->contract_file_path) Storage::disk('public')->delete($contract->contract_file_path);
        $contract->delete();
        return redirect()->route('contracts.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function pdf(Contract $contract)
    {
        $contract->load(['project', 'client', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','company_address','currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        $pdf = Pdf::loadView('pdf.contract', compact('contract', 'settings'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("contract-{$contract->contract_number}.pdf");
    }
}
