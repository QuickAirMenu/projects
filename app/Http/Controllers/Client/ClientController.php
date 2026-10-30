<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Mail\ClientWelcomeMail;
use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::with(['assignedUser', 'projects'])->latest();

        if ($request->search) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('company_name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
            );
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $clients = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Client::count(),
            'active' => Client::where('status', 'active')->count(),
            'vip' => Client::where('status', 'vip')->count(),
            'potential' => Client::where('status', 'potential')->count(),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', Client::class);

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('clients.create', compact('users'));
    }

    public function store(StoreClientRequest $request)
    {
        $this->authorize('create', Client::class);

        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $client = Client::create($data);

        if ($client->email && $request->boolean('send_invite')) {
            $this->sendPortalInvite($client);
        }

        return redirect()->route('clients.index')
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['projects.status', 'communications.user', 'assignedUser', 'portalAccount']);

        return view('clients.show', compact('client'));
    }

    public function resendPortalInvite(Client $client)
    {
        $this->authorize('update', $client);

        if (! $client->email) {
            return back()->with('error', __('messages.client_no_email'));
        }

        if ($client->portalAccount?->email_verified_at) {
            return back()->with('success', __('messages.portal_already_active'));
        }

        $this->sendPortalInvite($client);

        return back()->with('success', __('messages.client_invite_sent'));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('clients.edit', compact('client', 'users'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->update($request->validated());

        return redirect()->route('clients.show', $client)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    protected function sendPortalInvite(Client $client): void
    {
        $setupToken = Str::random(64);

        $account = ClientPortalAccount::firstOrNew(['client_id' => $client->id]);
        if (! $account->exists) {
            $account->password = Str::random(32);
        }

        $account->is_active = true;
        $account->email_verified_at = null;
        $account->setup_token = $setupToken;
        $account->setup_token_expires_at = now()->addDays(7);
        $account->save();

        try {
            app(MailConfigService::class)->applyDynamicMailConfig();

            if (! blank(config('mail.mailers.smtp.host'))) {
                Mail::to($client->email)->send(new ClientWelcomeMail($client, route('portal.setup', $account->setup_token)));
            }
        } catch (\Throwable $e) {
            Log::error('Client welcome email failed: '.$e->getMessage());
        }
    }
}
