<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreQuoteRequest;
use App\Models\Client;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\ServiceType;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $stats   = [
            'total'    => Quote::count(),
            'draft'    => Quote::where('status','draft')->count(),
            'approved' => Quote::where('status','approved')->count(),
            'pending'  => Quote::where('status','sent')->count(),
        ];
        return view('finance.quotes.index', compact('quotes','clients','stats'));
    }

    public function create()
    {
        $clients      = Client::where('status','!=','inactive')->orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active',true)->orderBy('sort_order')->get();
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
        $quote->load(['client','items','creator']);
        return view('finance.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $clients      = Client::orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active',true)->get();
        return view('finance.quotes.create', compact('quote','clients','serviceTypes'));
    }

    public function update(StoreQuoteRequest $request, Quote $quote)
    {
        $quote->items()->delete();
        $quote->update(array_diff_key($request->validated(), ['items'=>'']));
        foreach ($request->items as $i => $item) {
            $quote->items()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $item['quantity'] * $item['unit_price'],
                'sort_order'  => $i,
            ]);
        }
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
        return redirect()->route('quotes.show', $quote)
            ->with('success', 'تم إرسال العرض بنجاح');
    }

    public function convertToProject(Request $request, Quote $quote)
    {
        $request->validate([
            'status_id'       => 'required|exists:project_statuses,id',
            'service_type_id' => 'required|exists:service_types,id',
            'manager_id'      => 'required|exists:users,id',
        ]);
        $project = $this->quoteService->convertToProject($quote, $request->all());
        $quote->update(['status' => 'approved', 'approved_at' => now()]);
        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تحويل العرض إلى مشروع بنجاح');
    }
}
