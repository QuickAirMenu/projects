<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientCommunication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientCommunicationController extends Controller
{
    /**
     * Store a new communication log for a client.
     */
    public function store(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $request->validate([
            'type' => 'required|in:call,email,meeting,whatsapp,other',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'communication_date' => 'required|date',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        $client->communications()->create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'communication_date' => $request->communication_date,
            'follow_up_date' => $request->follow_up_date,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', __('client.communication_added'));
    }

    /**
     * Delete a communication log.
     */
    public function destroy(ClientCommunication $communication)
    {
        $this->authorize('update', $communication->client);

        $clientId = $communication->client_id;
        $communication->delete();

        return redirect()->route('clients.show', $clientId)
            ->with('success', __('client.communication_deleted'));
    }
}
