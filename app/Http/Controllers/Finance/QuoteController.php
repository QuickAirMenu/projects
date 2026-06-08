<?php

namespace App\Http\Controllers\Finance;

use App\Mail\QuoteSentMail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreQuoteRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\ServiceType;
use App\Services\MailConfigService;
use App\Services\QuoteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    public function __construct(protected QuoteService $quoteService) {}

    public function index(Request $request)
    {
        $query = Quote::with(['client','creator'])->latest();
        if ($request->status)    $query->where('status', $request->status);
        if ($request->client_id) $query->where('client_id', $request->client_id);
        $quotes  = $query->paginate(15)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $unconvertedQuotes = Quote::whereNull('converted_to_project_id');
        $stats   = [
            'total'                      => Quote::count(),
            'unconverted_count'          => (clone $unconvertedQuotes)->count(),
            'unconverted_amount'         => (clone $unconvertedQuotes)->sum('total'),
            'approved_unconverted_amount' => Quote::approved()
                ->whereNull('converted_to_project_id')
                ->sum('total'),
            'expired_unconverted_count'   => (clone $unconvertedQuotes)
                ->whereDate('valid_until', '<', now())
                ->count(),
            'conversion_rate'            => Quote::count() > 0
                ? round((Quote::whereNotNull('converted_to_project_id')->count() / Quote::count()) * 100, 1)
                : 0,
        ];
        return view('finance.quotes.index', compact('quotes','clients','stats'));
    }

    public function create()
    {
        $clients      = Client::where('status','!=','inactive')->orderBy('name')->get();
        $serviceTypes = ServiceType::with(['defaultItems' => fn($q) => $q->orderBy('sort_order')])
            ->where('is_active',true)
            ->orderBy('sort_order')
            ->get();
        return view('finance.quotes.create', compact('clients','serviceTypes'));
    }

    public function store(StoreQuoteRequest $request)
    {
        $quote = $this->quoteService->createQuote($request->validated());
        return redirect()->route('quotes.show', $quote)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Quote $quote)
    {
        $quote->load(['client','items.serviceType','creator','invoice']);

        $whatsappUrl = null;
        $sharePhone = $quote->client?->whatsapp ?: $quote->client?->phone;
        if ($sharePhone) {
            $phone = $this->normalizeWhatsAppNumber($sharePhone);
            $message = rawurlencode(
                "مرحباً {$quote->client->name}\n"
                . "عرض السعر رقم {$quote->quote_number}\n"
                . 'الإجمالي: ' . format_currency($quote->total) . "\n"
                . 'الرابط: ' . route('quotes.public', $quote->public_token)
            );
            if ($phone) {
                $whatsappUrl = "https://wa.me/{$phone}?text={$message}";
            }
        }

        return view('finance.quotes.show', compact('quote', 'whatsappUrl'));
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
            return '966' . ltrim($digits, '0');
        }

        return $digits;
    }

    public function pdf(Quote $quote)
    {
        $quote->load(['client', 'items.serviceType', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','company_address','currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'settings'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("quote-{$quote->quote_number}.pdf");
    }

    public function edit(Quote $quote)
    {
        $quote->load('items');
        $clients      = Client::orderBy('name')->get();
        $serviceTypes = ServiceType::with(['defaultItems' => fn($q) => $q->orderBy('sort_order')])->where('is_active',true)->orderBy('sort_order')->get();
        return view('finance.quotes.create', compact('quote','clients','serviceTypes'));
    }

    public function update(StoreQuoteRequest $request, Quote $quote)
    {
        $quote = $this->quoteService->updateQuote($quote, $request->validated());

        return redirect()->route('quotes.show', $quote)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('quotes.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function send(Quote $quote)
    {
        $quote->update(['status' => 'sent', 'sent_at' => now()]);

        $message = 'تم تحديث حالة العرض';
        if ($quote->client?->email) {
            try {
                app(MailConfigService::class)->applyDynamicMailConfig();
                if (config('mail.mailers.smtp.host')) {
                    Mail::to($quote->client->email)->send(new QuoteSentMail($quote->fresh(['client', 'items'])));
                    $message = 'تم إرسال العرض بنجاح';
                }
            } catch (\Throwable $e) {
                report($e);
                $message = 'تم تغيير الحالة إلى "مُرسل" لكن تعذر إرسال الإيميل، يرجى المحاولة لاحقاً';
            }
        }

        return redirect()->route('quotes.show', $quote)
            ->with('success', $message);
    }

    public function convertToInvoice(Quote $quote)
    {
        abort_unless($quote->status === 'approved', 403);

        if ($quote->invoice) {
            return redirect()->route('invoices.show', $quote->invoice)
                ->with('success', 'هذا العرض مرتبط بفاتورة بالفعل');
        }

        return redirect()->route('invoices.create', [
            'quote_id' => $quote->id,
            'client_id' => $quote->client_id,
            'description' => $quote->subject,
        ]);
    }

    public function publicShow(string $token)
    {
        $quote = Quote::with(['client', 'items.serviceType'])->where('public_token', $token)->firstOrFail();

        abort_if($quote->status === 'draft', 404);

        return view('finance.quotes.public', compact('quote'));
    }

    public function publicPdf(string $token)
    {
        $quote = Quote::with(['client', 'items.serviceType', 'createdBy'])
            ->where('public_token', $token)
            ->firstOrFail();

        abort_if($quote->status === 'draft', 404);

        $settings = Setting::whereIn('key', [
            'company_name','company_name_ar','company_logo',
            'company_email','company_phone','company_address','currency_symbol'
        ])->pluck('value', 'key')->toArray();

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'settings'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("quote-{$quote->quote_number}.pdf");
    }

    public function convertToProject(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'status_id'       => 'required|exists:project_statuses,id',
            'service_type_id' => 'required|exists:service_types,id',
            'manager_id'      => 'required|exists:users,id',
        ]);
        $project = $this->quoteService->convertToProject($quote, $data);
        $quote->update(['status' => 'approved', 'approved_at' => now()]);
        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تحويل العرض إلى مشروع بنجاح');
    }
}
