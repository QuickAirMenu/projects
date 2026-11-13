<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:60'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->is_active || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $canViewFinancials = $user->hasAnyRole(['super-admin', 'admin', 'finance']);

        $projects = Project::active();
        $tasks = Task::whereNull('parent_task_id');

        if (! $canViewFinancials) {
            $projects->where(fn ($q) => $q
                ->where('manager_id', $user->id)
                ->orWhereHas('teamMembers', fn ($t) => $t->where('users.id', $user->id)));
            $tasks->where('assigned_to', $user->id);
        }

        $payload = [
            'projects_total' => (clone $projects)->count(),
            'tasks_total' => (clone $tasks)->count(),
            'tasks_overdue' => (clone $tasks)->whereDate('due_date', '<', now())
                ->whereHas('status', fn ($q) => $q->where('slug', '!=', 'done'))->count(),
        ];

        if ($canViewFinancials) {
            $payload['financial'] = [
                'invoiced' => (float) Invoice::sum('total'),
                'collected' => (float) Invoice::sum('paid_amount'),
                'outstanding' => (float) Invoice::whereIn('payment_status', ['unpaid', 'partially_paid', 'overdue'])->sum('remaining_amount'),
            ];
        }

        return response()->json($payload);
    }

    public function projects(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Project::with(['client:id,name', 'status:id,name_ar,name_en'])
            ->select(['id', 'name', 'project_number', 'client_id', 'status_id', 'deadline', 'progress_percentage', 'priority'])
            ->latest();

        if (! $user->hasAnyRole(['super-admin', 'admin', 'finance'])) {
            $query->where(fn ($q) => $q
                ->where('manager_id', $user->id)
                ->orWhereHas('teamMembers', fn ($t) => $t->where('users.id', $user->id)));
        }

        return $this->paginated($query->paginate(20), fn (Project $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'number' => $p->project_number,
            'client' => $p->client?->name,
            'status' => $p->status?->name_ar ?: $p->status?->name_en,
            'priority' => $p->priority,
            'progress' => (int) $p->progress_percentage,
            'deadline' => $p->deadline?->format('Y-m-d'),
        ]);
    }

    public function clients(): JsonResponse
    {
        $query = Client::query()->latest()->select(['id', 'name', 'company_name', 'email', 'phone', 'status']);

        return $this->paginated($query->paginate(20), fn (Client $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'company' => $c->company_name,
            'email' => $c->email,
            'phone' => $c->phone,
            'status' => $c->status,
        ]);
    }

    public function invoices(): JsonResponse
    {
        $query = Invoice::with(['client:id,name'])
            ->latest('invoice_date')
            ->select(['id', 'invoice_number', 'client_id', 'total', 'paid_amount', 'remaining_amount', 'payment_status', 'invoice_date', 'due_date']);

        return $this->paginated($query->paginate(20), fn (Invoice $i) => [
            'id' => $i->id,
            'number' => $i->invoice_number,
            'client' => $i->client?->name,
            'total' => (float) $i->total,
            'paid' => (float) $i->paid_amount,
            'remaining' => (float) $i->remaining_amount,
            'status' => $i->payment_status,
            'date' => $i->invoice_date?->format('Y-m-d'),
            'due_date' => $i->due_date?->format('Y-m-d'),
        ]);
    }

    public function quotes(): JsonResponse
    {
        $query = Quote::with(['client:id,name'])
            ->latest()
            ->select(['id', 'quote_number', 'subject', 'client_id', 'total', 'status', 'valid_until', 'created_at']);

        return $this->paginated($query->paginate(20), fn (Quote $q) => [
            'id' => $q->id,
            'number' => $q->quote_number,
            'subject' => $q->subject,
            'client' => $q->client?->name,
            'total' => (float) $q->total,
            'status' => $q->status,
            'valid_until' => $q->valid_until?->format('Y-m-d'),
        ]);
    }

    public function tasks(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Task::with(['project:id,name', 'status:id,name_ar,name_en'])
            ->whereNull('parent_task_id')
            ->latest()
            ->select(['id', 'title', 'project_id', 'status_id', 'priority', 'assigned_to', 'due_date', 'progress_percentage']);

        if (! $user->hasAnyRole(['super-admin', 'admin'])) {
            $query->where('assigned_to', $user->id);
        }

        return $this->paginated($query->paginate(20), fn (Task $t) => [
            'id' => $t->id,
            'title' => $t->title,
            'project' => $t->project?->name,
            'status' => $t->status?->name_ar ?: $t->status?->name_en,
            'priority' => $t->priority,
            'progress' => (int) $t->progress_percentage,
            'due_date' => $t->due_date?->format('Y-m-d'),
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'language' => $user->language,
            'roles' => $user->getRoleNames(),
        ];
    }

    private function paginated($paginator, callable $mapper): JsonResponse
    {
        return response()->json([
            'data' => collect($paginator->items())->map($mapper)->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
