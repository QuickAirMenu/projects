<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreQuoteRequest;
use App\Mail\QuoteSentMail;
use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteView;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Services\MailConfigService;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;

class QuoteController extends Controller
{
    public function __construct(protected QuoteService $quoteService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Quote::class);

        $query = Quote::with(['client', 'creator'])->latest();
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->trashed) {
            $query->onlyTrashed();
        }
        $quotes = $query->paginate(15)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $unconvertedQuotes = Quote::whereNull('converted_to_project_id');
        $stats = [
            'total' => Quote::count(),
            'unconverted_count' => (clone $unconvertedQuotes)->count(),
            'unconverted_amount' => (clone $unconvertedQuotes)->sum('total'),
            'approved_unconverted_amount' => Quote::approved()
                ->whereNull('converted_to_project_id')
                ->sum('total'),
            'expired_unconverted_count' => (clone $unconvertedQuotes)
                ->whereDate('valid_until', '<', now())
                ->count(),
            'conversion_rate' => Quote::count() > 0
                ? round((Quote::whereNotNull('converted_to_project_id')->count() / Quote::count()) * 100, 1)
                : 0,
        ];
        $trashed = $request->has('trashed');

        return view('finance.quotes.index', compact('quotes', 'clients', 'stats', 'trashed'));
    }

    public function create()
    {
        $this->authorize('create', Quote::class);

        $clients = Client::where('status', '!=', 'inactive')->orderBy('name')->get();
        $serviceTypes = ServiceType::with(['defaultItems' => fn ($q) => $q->orderBy('sort_order')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('finance.quotes.create', compact('clients', 'serviceTypes'));
    }

    public function store(StoreQuoteRequest $request)
    {
        $this->authorize('create', Quote::class);

        $quote = $this->quoteService->createQuote($request->validated());

        return redirect()->route('quotes.show', $quote)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Quote $quote)
    {
        $this->authorize('view', $quote);

        $quote->load(['client', 'items.serviceType', 'creator', 'invoice', 'approvedBy']);

        $recentViews = $quote->views()->latest('viewed_at')->limit(10)->get();

        $whatsappUrl = null;
        $sharePhone = $quote->client?->whatsapp ?: $quote->client?->phone;
        if ($sharePhone) {
            $phone = $this->normalizeWhatsAppNumber($sharePhone);
            $message = rawurlencode(
                "مرحباً {$quote->client->name}،\n\n"
                ."تجدون مرفقاً عرض السعر رقم {$quote->quote_number}\n"
                .($quote->subject ? "الموضوع: {$quote->subject}\n" : '')
                .'الإجمالي: '.format_currency($quote->total)."\n\n"
                ."للاطلاع على التفاصيل وتحميل نسخة PDF:\n"
                .route('quotes.public', $quote->public_token)."\n\n"
                .'مع تحيات فريق '.config('app.name')
            );
            if ($phone) {
                $whatsappUrl = "https://wa.me/{$phone}?text={$message}";
            }
        }

        return view('finance.quotes.show', compact('quote', 'whatsappUrl', 'recentViews'));
    }

    public function pdf(Quote $quote)
    {
        $this->authorize('view', $quote);

        $quote->load(['client', 'items.serviceType', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name', 'company_name_ar', 'company_logo', 'company_email', 'company_phone', 'company_address', 'currency_symbol'])
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

    public function edit(Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote->load('items');
        $clients = Client::orderBy('name')->get();
        $serviceTypes = ServiceType::with(['defaultItems' => fn ($q) => $q->orderBy('sort_order')])->where('is_active', true)->orderBy('sort_order')->get();

        return view('finance.quotes.create', compact('quote', 'clients', 'serviceTypes'));
    }

    public function update(StoreQuoteRequest $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote = $this->quoteService->updateQuote($quote, $request->validated());

        if ($request->input('action') === 'send' && in_array($quote->status, ['draft', 'sent'])) {
            $quote->update(['status' => 'sent', 'sent_at' => now()]);

            if ($quote->client?->email) {
                try {
                    app(MailConfigService::class)->applyDynamicMailConfig();
                    if (config('mail.mailers.smtp.host')) {
                        Mail::to($quote->client->email)->send(new QuoteSentMail($quote->fresh(['client', 'items'])));
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return redirect()->route('quotes.show', $quote)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Quote $quote)
    {
        $this->authorize('delete', $quote);

        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function restore(Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote->restore();

        return redirect()->route('quotes.index')->with('success', __('messages.restored_successfully'));
    }

    public function send(Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote->update(['status' => 'sent', 'sent_at' => now()]);

        $message = __('finance.quote_status_updated');
        if ($quote->client?->email) {
            try {
                app(MailConfigService::class)->applyDynamicMailConfig();
                if (config('mail.mailers.smtp.host')) {
                    Mail::to($quote->client->email)->send(new QuoteSentMail($quote->fresh(['client', 'items'])));
                    $message = __('finance.quote_sent_success');
                }
            } catch (\Throwable $e) {
                report($e);
                $message = __('finance.quote_sent_email_failed');
            }
        }

        return redirect()->route('quotes.show', $quote)
            ->with('success', $message);
    }

    public function sendWhatsApp(Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote->update(['status' => 'sent', 'sent_at' => now()]);

        $sharePhone = $quote->client?->whatsapp ?: $quote->client?->phone;
        if (! $sharePhone) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', __('finance.no_whatsapp_number'));
        }

        $phone = $this->normalizeWhatsAppNumber($sharePhone);
        $message = rawurlencode(
            "مرحباً {$quote->client->name}،\n\n"
            ."تجدون مرفقاً عرض السعر رقم {$quote->quote_number}\n"
            .($quote->subject ? "الموضوع: {$quote->subject}\n" : '')
            .'الإجمالي: '.format_currency($quote->total)."\n\n"
            ."للاطلاع على التفاصيل وتحميل نسخة PDF:\n"
            .route('quotes.public', $quote->public_token)."\n\n"
            .'مع تحيات فريق '.config('app.name')
        );

        $whatsappUrl = $phone ? "https://wa.me/{$phone}?text={$message}" : null;

        if (! $whatsappUrl) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', __('finance.invalid_whatsapp_number'));
        }

        return redirect()->away($whatsappUrl);
    }

    public function approve(Quote $quote)
    {
        $this->authorize('update', $quote);
        abort_unless($quote->status === 'sent', 403);

        $quote->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('quotes.show', $quote)
            ->with('success', __('finance.approved_successfully'));
    }

    public function publicApprove(Request $request, string $token)
    {
        $quote = Quote::where('public_token', $token)->firstOrFail();
        abort_unless($quote->status === 'sent', 403);

        $quote->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by_ip' => $request->ip(),
        ]);

        return redirect()->route('quotes.public', $quote->public_token)
            ->with('success', __('finance.quote_approved_thanks'));
    }

    public function convertToInvoice(Quote $quote)
    {
        $this->authorize('view', $quote);
        abort_unless($quote->status === 'approved', 403);

        if ($quote->invoice) {
            return redirect()->route('invoices.show', $quote->invoice)
                ->with('success', __('finance.quote_already_has_invoice'));
        }

        return redirect()->route('invoices.create', [
            'quote_id' => $quote->id,
            'client_id' => $quote->client_id,
            'description' => $quote->subject,
        ]);
    }

    public function publicApproveForm(string $token)
    {
        $quote = Quote::with(['client', 'items.serviceType'])->where('public_token', $token)->firstOrFail();
        abort_if($quote->status === 'draft', 404);

        return view('finance.quotes.approve', compact('quote'));
    }

    public function publicReject(Request $request, string $token)
    {
        $quote = Quote::where('public_token', $token)->firstOrFail();
        abort_unless($quote->status === 'sent', 403);

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $quote->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return redirect()->route('quotes.public', $quote->public_token)
            ->with('info', __('finance.modification_requested'));
    }

    public function redraft(Quote $quote)
    {
        $this->authorize('update', $quote);
        abort_unless($quote->status === 'rejected', 403);

        $quote->update([
            'status' => 'draft',
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return redirect()->route('quotes.edit', $quote)
            ->with('success', __('finance.quote_redrafted'));
    }

    public function publicShow(string $token)
    {
        $quote = Quote::with(['client', 'items.serviceType'])->where('public_token', $token)->firstOrFail();
        abort_if($quote->status === 'draft', 404);

        QuoteView::create([
            'quote_id' => $quote->id,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
            'viewed_at' => now(),
        ]);

        return view('finance.quotes.public', compact('quote'));
    }

    public function publicPdf(string $token)
    {
        $quote = Quote::with(['client', 'items.serviceType', 'createdBy'])
            ->where('public_token', $token)
            ->firstOrFail();

        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar', 'company_logo',
            'company_email', 'company_phone', 'company_address', 'currency_symbol',
        ])->pluck('value', 'key')->toArray();

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

    public function convertToProject(Request $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        $data = $request->validate([
            'status_id' => 'required|exists:project_statuses,id',
            'service_type_id' => 'required|exists:service_types,id',
            'manager_id' => 'required|exists:users,id',
        ]);
        $project = $this->quoteService->convertToProject($quote, $data);
        $quote->update(['status' => 'approved', 'approved_at' => now()]);

        return redirect()->route('projects.show', $project)
            ->with('success', __('finance.quote_converted_to_project'));
    }

    private function normalizeWhatsAppNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '966')) {
            return $digits;
        }

        if (preg_match('/^0?5\d{8}$/', $digits)) {
            return '966'.ltrim($digits, '0');
        }

        return $digits;
    }
}
