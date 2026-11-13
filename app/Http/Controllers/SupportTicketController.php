<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Notifications\SupportTicketReplied;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['client:id,name', 'replier:id,name'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(20)->withQueryString();
        $openCount = SupportTicket::where('status', 'open')->count();

        return view('support-tickets.index', compact('tickets', 'openCount'));
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'max:3000'],
        ]);

        $supportTicket->update([
            'admin_reply' => $data['admin_reply'],
            'replied_by' => $request->user()->id,
            'replied_at' => now(),
            'status' => 'answered',
        ]);

        $account = $supportTicket->portalAccount;

        if ($account) {
            try {
                $account->notify(new SupportTicketReplied($supportTicket));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', __('messages.ticket_replied'));
    }

    public function close(SupportTicket $supportTicket)
    {
        $supportTicket->update(['status' => 'closed']);

        return back()->with('success', __('messages.ticket_closed'));
    }
}
