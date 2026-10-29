<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Models\Quote;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

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
                        $statusQuery->whereNotIn('slug', ['delivered', 'archived']);
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
            ->select(['id', 'project_number', 'name', 'client_id', 'description', 'deadline', 'payment_status', 'progress_percentage', 'status_id', 'is_handed_off', 'handed_off_at'])
            ->whereKey($project->id)
            ->where('client_id', $portalAccount->client_id)
            ->with([
                'status',
                'teamMembers:id,name',
                'workflowSteps:id,project_id,step_name,step_order,completed',
                'attachments:id,project_id,file_name,file_size,created_at',
                'invoices:id,project_id,invoice_number,total,remaining_amount,payment_status',
                'quotes:id,converted_to_project_id,quote_number,subject,total,status',
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
        $showInvoices = portal_setting('portal_show_invoices') === '1';
        $showQuotes = portal_setting('portal_show_quotes') === '1';

        return view('portal.project', compact('project', 'paymentStatus', 'showInvoices', 'showQuotes'));
    }

    public function downloadAttachment(ProjectAttachment $attachment)
    {
        $portalAccount = Auth::guard('client')->user();

        abort_unless($attachment->project->client_id === $portalAccount->client_id, 403);

        if (!$attachment->file_path) {
            abort(404);
        }

        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->file_name ?? basename($attachment->file_path)
        );
    }

    public function invoices()
    {
        $portalAccount = Auth::guard('client')->user();

        if (portal_setting('portal_show_invoices') !== '1') {
            return redirect()->route('portal.dashboard');
        }

        $invoices = Invoice::query()
            ->select(['id', 'project_id', 'invoice_number', 'invoice_date', 'total', 'remaining_amount', 'payment_status', 'due_date'])
            ->whereHas('project', fn($q) => $q->where('client_id', $portalAccount->client_id))
            ->with('project:id,name')
            ->latest()
            ->paginate(15);

        return view('portal.invoices', compact('invoices'));
    }

    public function downloadInvoicePdf(Invoice $invoice)
    {
        $portalAccount = Auth::guard('client')->user();

        abort_unless(
            $invoice->project && $invoice->project->client_id === $portalAccount->client_id,
            403
        );

        $invoice->load(['client', 'project', 'items', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','company_address','currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $html = view('pdf.invoice', compact('invoice', 'settings'))->render();
        $mpdf->WriteHTML($html);
        return response($mpdf->Output("invoice-{$invoice->invoice_number}.pdf", 'S'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function quotes()
    {
        $portalAccount = Auth::guard('client')->user();

        if (portal_setting('portal_show_quotes') !== '1') {
            return redirect()->route('portal.dashboard');
        }

        $quotes = Quote::query()
            ->select(['id', 'client_id', 'quote_number', 'subject', 'total', 'status', 'valid_until', 'created_at', 'public_token'])
            ->where('client_id', $portalAccount->client_id)
            ->latest()
            ->paginate(15);

        return view('portal.quotes', compact('quotes'));
    }

    public function downloadQuotePdf(Quote $quote)
    {
        $portalAccount = Auth::guard('client')->user();

        abort_unless($quote->client_id === $portalAccount->client_id, 403);

        $quote->load(['client', 'items.serviceType', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','company_address','currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $html = view('pdf.quote', compact('quote', 'settings'))->render();
        $mpdf->WriteHTML($html);
        $filename = "quote-{$quote->quote_number}.pdf";
        return response($mpdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }

    public function profile()
    {
        $portalAccount = Auth::guard('client')->user();
        $client = Client::findOrFail($portalAccount->client_id);
        return view('portal.profile', compact('client', 'portalAccount'));
    }

    public function updateProfile(Request $request)
    {
        $portalAccount = Auth::guard('client')->user();
        $client = Client::findOrFail($portalAccount->client_id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $client->update($data);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function updatePassword(Request $request)
    {
        $portalAccount = Auth::guard('client')->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $portalAccount->password)) {
            return back()->withErrors(['current_password' => __('auth.password')])->onlyInput('current_password');
        }

        $portalAccount->update(['password' => $data['password']]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function showForgotPassword()
    {
        return view('portal.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $client = Client::where('email', $data['email'])->first();
        if (!$client) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $account = ClientPortalAccount::where('client_id', $client->id)->first();
        if (!$account) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $token = Str::random(40);
        $account->update([
            'setup_token' => $token,
            'setup_token_expires_at' => now()->addHours(2),
        ]);

        try {
            $settings = Setting::whereIn('key', ['company_name', 'company_name_ar', 'company_logo', 'company_email'])
                ->pluck('value', 'key')
                ->toArray();

            Mail::send('emails.portal-reset-password', [
                'client' => $client,
                'token' => $token,
                'settings' => $settings,
            ], function ($message) use ($client, $settings) {
                $message->to($client->email, $client->name)
                    ->subject(app()->isLocale('ar')
                        ? 'إعادة تعيين كلمة المرور - بوابة العميل'
                        : 'Reset Your Password - Client Portal');
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', __('messages.sent_successfully'));
    }

    public function showResetPassword(string $token)
    {
        $account = ClientPortalAccount::with('client')
            ->where('setup_token', $token)
            ->where('setup_token_expires_at', '>', now())
            ->firstOrFail();

        return view('portal.reset-password', compact('account', 'token'));
    }

    public function completeResetPassword(Request $request, string $token)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $account = ClientPortalAccount::where('setup_token', $token)
            ->where('setup_token_expires_at', '>', now())
            ->firstOrFail();

        $account->update([
            'password' => $data['password'],
            'setup_token' => null,
            'setup_token_expires_at' => null,
        ]);

        return redirect()->route('portal.login')->with('success', __('messages.updated_successfully'));
    }

    public function payments()
    {
        $portalAccount = Auth::guard('client')->user();

        $payments = Payment::query()
            ->select(['id', 'invoice_id', 'payment_number', 'amount', 'payment_date', 'payment_method', 'transaction_reference', 'proof_file_path', 'proof_file_name', 'notes'])
            ->whereHas('invoice.project', fn($q) => $q->where('client_id', $portalAccount->client_id))
            ->with('invoice:id,invoice_number')
            ->latest()
            ->paginate(15);

        return view('portal.payments', compact('payments'));
    }

    public function downloadPaymentProof(Payment $payment)
    {
        $portalAccount = Auth::guard('client')->user();

        abort_unless(
            $payment->invoice?->project?->client_id === $portalAccount->client_id,
            403
        );

        if (!$payment->proof_file_path) {
            abort(404);
        }

        return Storage::disk('private')->download(
            $payment->proof_file_path,
            $payment->proof_file_name ?? 'proof-' . $payment->payment_number . '.' . pathinfo($payment->proof_file_path, PATHINFO_EXTENSION)
        );
    }

    public function contracts()
    {
        $portalAccount = Auth::guard('client')->user();

        $contracts = Contract::query()
            ->select(['id', 'contract_number', 'client_id', 'project_id', 'title', 'contract_value', 'status', 'start_date', 'end_date', 'contract_file_path'])
            ->where('client_id', $portalAccount->client_id)
            ->with('project:id,name')
            ->latest()
            ->paginate(15);

        return view('portal.contracts', compact('contracts'));
    }

    public function downloadContractPdf(Contract $contract)
    {
        $portalAccount = Auth::guard('client')->user();

        abort_unless($contract->client_id === $portalAccount->client_id, 403);

        if ($contract->contract_file_path) {
            return Storage::disk('private')->download($contract->contract_file_path, "contract-{$contract->contract_number}.pdf");
        }

        $contract->load(['client', 'project', 'invoice', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','company_address','currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $html = view('pdf.contract', compact('contract', 'settings'))->render();
        $mpdf->WriteHTML($html);
        return response($mpdf->Output("contract-{$contract->contract_number}.pdf", 'S'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
