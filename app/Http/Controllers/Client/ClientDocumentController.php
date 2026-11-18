<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientDocumentController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', ClientDocument::TYPES)],
            'title' => ['required', 'string', 'max:150'],
            'number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('clients/'.$client->id.'/documents', 'private');

        $client->documents()->create([
            'type' => $data['type'],
            'title' => $data['title'],
            'number' => $data['number'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'created_by' => Auth::id(),
        ]);

        activity()->causedBy(Auth::user())->performedOn($client)->log('client document uploaded');

        return back()->with('success', __('client.document_uploaded'));
    }

    public function destroy(Client $client, ClientDocument $document)
    {
        $this->authorize('update', $client);

        abort_unless((int) $document->client_id === (int) $client->id, 404);

        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return back()->with('success', __('client.document_deleted'));
    }

    public function download(Client $client, ClientDocument $document)
    {
        $this->authorize('view', $client);

        abort_unless((int) $document->client_id === (int) $client->id, 404);
        abort_unless(Storage::disk('private')->exists($document->file_path), 404);

        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }
}
