<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeRequest\StoreChangeRequestRequest;
use App\Models\ChangeRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ChangeRequestService;

class ChangeRequestController extends Controller
{
    public function __construct(protected ChangeRequestService $changeRequestService) {}

    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('view-change-requests'), 403);

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
        abort_unless(Auth::user()->can('create-change-requests'), 403);

        $projects = Project::orderBy('name')->get();
        return view('change-requests.create', compact('projects'));
    }

    public function store(StoreChangeRequestRequest $request)
    {
        abort_unless(Auth::user()->can('create-change-requests'), 403);

        $this->changeRequestService->create($request->validated());

        return redirect()->route('change-requests.index')
            ->with('success', __('common.created_successfully'));
    }

    public function show(ChangeRequest $changeRequest)
    {
        abort_unless(Auth::user()->can('view-change-requests'), 403);

        $changeRequest->load(['project', 'project.client', 'creator', 'approver']);
        return view('change-requests.show', compact('changeRequest'));
    }

    public function approve(Request $request, ChangeRequest $changeRequest)
    {
        abort_unless(Auth::user()->hasAnyRole(['super-admin', 'admin', 'finance']), 403);

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
        abort_unless(Auth::user()->hasAnyRole(['super-admin', 'admin', 'finance']), 403);

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
