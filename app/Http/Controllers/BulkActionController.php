<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    public function bulkDestroyProjects(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', __('messages.no_items_selected'));
        }

        $query = Project::whereIn('id', $ids);
        $this->applyProjectScope($query);
        $count = $query->count();

        if ($count === 0) {
            return back()->with('error', __('messages.unauthorized'));
        }

        $query->delete();

        return back()->with('success', __('messages.bulk_deleted', ['count' => $count]));
    }

    public function bulkDestroyInvoices(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', __('messages.no_items_selected'));
        }

        Invoice::whereIn('id', $ids)->delete();

        return back()->with('success', __('messages.bulk_deleted', ['count' => count($ids)]));
    }

    protected function applyProjectScope($query)
    {
        $user = auth()->user();
        if (! $user->hasAnyRole(['super-admin', 'admin', 'finance'])) {
            $query->where(fn ($q) => $q
                ->where('manager_id', $user->id)
                ->orWhereHas('teamMembers', fn ($t) => $t->where('users.id', $user->id))
            );
        }
    }
}
