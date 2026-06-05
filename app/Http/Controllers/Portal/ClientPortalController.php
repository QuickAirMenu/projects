<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPortalController extends Controller
{
    public function showLogin()
    {
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $client = Client::where('email', $data['email'])->first();
        if (!$client) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $account = ClientPortalAccount::where('client_id', $client->id)->first();
        if (!$account || !$account->is_active || !$account->email_verified_at) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        if (!Auth::guard('client')->attempt(['client_id' => $client->id, 'password' => $data['password']])) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();
        Auth::guard('client')->user()?->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('portal.dashboard');
    }

    public function showSetup(string $token)
    {
        $account = ClientPortalAccount::with('client')
            ->where('setup_token', $token)
            ->where('setup_token_expires_at', '>', now())
            ->firstOrFail();

        return view('portal.setup', compact('account', 'token'));
    }

    public function completeSetup(Request $request, string $token)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $account = ClientPortalAccount::where('setup_token', $token)
            ->where('setup_token_expires_at', '>', now())
            ->firstOrFail();

        $account->update([
            'password' => $data['password'],
            'email_verified_at' => now(),
            'setup_token' => null,
            'setup_token_expires_at' => null,
        ]);

        return redirect()->route('portal.login')->with('success', __('messages.updated_successfully'));
    }

    public function dashboard()
    {
        $portalAccount = Auth::guard('client')->user();

        $client = Client::query()
            ->select(['id', 'name'])
            ->with(['projects' => function ($query) {
                $query->select(['id', 'client_id', 'name', 'deadline', 'progress_percentage', 'status_id'])
                    ->with('status')
                    ->whereHas('status', function ($statusQuery) {
                        $statusQuery->whereNotIn('slug', ['completed', 'delivered', 'archived']);
                    })
                    ->orderByRaw('deadline is null, deadline asc');
            }])
            ->findOrFail($portalAccount->client_id);

        return view('portal.dashboard', compact('client'));
    }

    public function projectShow(Project $project)
    {
        $portalAccount = Auth::guard('client')->user();

        abort_unless($project->client_id === $portalAccount->client_id, 403);

        $project = Project::query()
            ->select(['id', 'project_number', 'name', 'client_id', 'description', 'deadline', 'payment_status', 'progress_percentage', 'status_id'])
            ->whereKey($project->id)
            ->where('client_id', $portalAccount->client_id)
            ->with([
                'status',
                'teamMembers:id,name',
                'workflowSteps:id,project_id,step_name,step_order,completed',
                'invoices:id,project_id,payment_status',
            ])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($query) {
                    $query->whereHas('status', function ($statusQuery) {
                        $statusQuery->where('slug', 'done');
                    });
                },
            ])
            ->firstOrFail();

        $paymentStatus = $project->invoices->isNotEmpty() && $project->invoices->every(fn ($invoice) => $invoice->payment_status === 'paid') ? 'paid' : 'pending';

        return view('portal.project', compact('project', 'paymentStatus'));
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
