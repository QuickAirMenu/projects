<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ChangeRequest::with(['project', 'project.client', 'creator', 'approver'])
            ->latest();

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->scope_type) {
            $query->where('scope_type', $request->scope_type);
        }

        $changeRequests = $query->paginate(15);
        $projects       = Project::orderBy('name')->get();

        return view('change-requests.index', compact('changeRequests', 'projects'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        return view('change-requests.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'requested_by'     => 'required|in:client,internal',
            'scope_type'       => 'required|in:in_scope,out_of_scope',
            'priority'         => 'required|in:low,medium,high',
            'impact_analysis'  => 'nullable|string',
            'additional_cost'  => 'nullable|numeric|min:0',
            'additional_hours' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();
        $data['created_by']     = Auth::id();
        $data['request_number'] = 'CR-' . date('Ym') . '-' . str_pad(ChangeRequest::count() + 1, 4, '0', STR_PAD_LEFT);

        ChangeRequest::create($data);

        return redirect()->route('change-requests.index')
            ->with('success', __('common.created_successfully'));
    }

    public function show(ChangeRequest $changeRequest)
    {
        $changeRequest->load(['project', 'project.client', 'creator', 'approver']);
        return view('change-requests.show', compact('changeRequest'));
    }

    public function approve(Request $request, ChangeRequest $changeRequest)
    {
        $changeRequest->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('change-requests.show', $changeRequest)
            ->with('success', __('common.approved_successfully'));
    }

    public function reject(Request $request, ChangeRequest $changeRequest)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $changeRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => Auth::id(),
            'approved_at'      => now(),
        ]);

        return redirect()->route('change-requests.show', $changeRequest)
            ->with('success', __('common.rejected_successfully'));
    }
}
