<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['assignedUser', 'projects'])->latest();

        if ($request->search) {
            $query->where(fn($q) => $q
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
            'total'    => Client::count(),
            'active'   => Client::where('status', 'active')->count(),
            'vip'      => Client::where('status', 'vip')->count(),
            'potential'=> Client::where('status', 'potential')->count(),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('clients.create', compact('users'));
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        Client::create($data);

        return redirect()->route('clients.index')
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Client $client)
    {
        $client->load(['projects.status', 'communications.user', 'assignedUser']);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('clients.edit', compact('client', 'users'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return redirect()->route('clients.show', $client)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', __('messages.deleted_successfully'));
    }
}
