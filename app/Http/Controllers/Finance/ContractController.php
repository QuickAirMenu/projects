<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::with(['project','client','creator'])->latest()->paginate(15);
        return view('finance.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $clients  = Client::orderBy('name')->get();
        return view('finance.contracts.create', compact('projects','clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'client_id'      => 'required|exists:clients,id',
            'title'          => 'required|string|max:255',
            'start_date'     => 'required|date',
            'contract_value' => 'required|numeric|min:0',
            'status'         => 'required|in:draft,active,completed,terminated',
            'contract_file'  => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $request->except('contract_file');
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
        $contract->load(['project','client','creator']);
        return view('finance.contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $projects = Project::orderBy('name')->get();
        $clients  = Client::orderBy('name')->get();
        return view('finance.contracts.create', compact('contract','projects','clients'));
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate(['title'=>'required','start_date'=>'required|date','contract_value'=>'required|numeric']);
        $data = $request->except('contract_file');
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
}
