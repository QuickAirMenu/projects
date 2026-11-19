<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::withCount('transactions')->orderBy('name')->get();
        $baseCurrency = app(TreasuryService::class)->baseCurrency();

        $assetsTotal = $accounts->filter(fn (Account $a) => $a->nature() === 'asset')->sum(fn (Account $a) => $a->baseBalance());
        $liabilitiesTotal = $accounts->filter(fn (Account $a) => $a->nature() === 'liability')->sum(fn (Account $a) => $a->baseBalance());
        $netTotal = $assetsTotal + $liabilitiesTotal;

        return view('finance.accounts.index', compact('accounts', 'assetsTotal', 'liabilitiesTotal', 'netTotal', 'baseCurrency'));
    }

    public function create()
    {
        return view('finance.accounts.form', [
            'account' => new Account(['type' => 'bank', 'currency' => app(TreasuryService::class)->baseCurrency(), 'is_active' => true, 'exchange_rate_to_base' => 1]),
        ]);
    }

    public function store(Request $request)
    {
        Account::create($this->validated($request) + ['created_by' => Auth::id()]);

        return redirect()->route('accounts.index')->with('success', __('accounts.created'));
    }

    public function show(Request $request, Account $account)
    {
        $query = $account->transactions()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date('to')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->input('status') === 'unreconciled', fn ($q) => $q->whereNull('reconciled_at'));

        $rows = $query->orderBy('transaction_date')->orderBy('id')->get();

        $running = (float) $account->opening_balance;
        $withBalance = $rows->map(function (AccountTransaction $tx) use (&$running) {
            $running += $tx->direction === 'in' ? (float) $tx->amount : -(float) $tx->amount;

            return ['tx' => $tx, 'balance' => $running];
        })->reverse()->values();

        $baseCurrency = app(TreasuryService::class)->baseCurrency();

        return view('finance.accounts.show', [
            'account' => $account,
            'rows' => $withBalance,
            'balance' => $account->balance(),
            'baseBalance' => $account->baseBalance(),
            'baseCurrency' => $baseCurrency,
            'filters' => $request->only(['from', 'to', 'category', 'status']),
        ]);
    }

    public function edit(Account $account)
    {
        return view('finance.accounts.form', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $account->update($this->validated($request));

        return redirect()->route('accounts.show', $account)->with('success', __('accounts.updated'));
    }

    public function destroy(Account $account)
    {
        if ($account->transactions()->exists()) {
            return back()->with('error', __('accounts.cannot_delete'));
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', __('accounts.deleted'));
    }

    public function storeTransaction(Request $request, Account $account, TreasuryService $treasury)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.000001'],
            'category' => ['required', 'in:client_payment,team_payout,expense,transfer,adjustment,other'],
            'description' => ['nullable', 'string', 'max:300'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('accounts/'.$account->id, 'private');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        $treasury->record($account, $data + ['exchange_rate' => $data['exchange_rate'] ?? $account->exchange_rate_to_base]);

        return back()->with('success', __('accounts.transaction_added'));
    }

    public function transferForm()
    {
        return view('finance.accounts.transfer', ['accounts' => Account::where('is_active', true)->orderBy('name')->get()]);
    }

    public function transfer(Request $request, TreasuryService $treasury)
    {
        $data = $request->validate([
            'from_account_id' => ['required', 'exists:accounts,id'],
            'to_account_id' => ['required', 'exists:accounts,id', 'different:from_account_id'],
            'amount_out' => ['required', 'numeric', 'min:0.01'],
            'amount_in' => ['nullable', 'numeric', 'min:0.01'],
            'rate' => ['nullable', 'numeric', 'min:0.000001'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:300'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachment = [
                'path' => $file->store('accounts/'.$data['from_account_id'], 'private'),
                'name' => $file->getClientOriginalName(),
            ];
        }

        if (! empty($data['rate'])) {
            $data['amount_in'] = round((float) $data['amount_out'] * (float) $data['rate'], 2);
        }

        if (empty($data['amount_in'])) {
            return back()->withInput()->withErrors(['amount_in' => __('accounts.rate_or_amount_required')]);
        }

        $from = Account::findOrFail($data['from_account_id']);
        $to = Account::findOrFail($data['to_account_id']);

        $treasury->transfer($from, $to, (float) $data['amount_out'], (float) $data['amount_in'], $data['transaction_date'], $data['description'] ?? null, $attachment);

        return redirect()->route('accounts.show', $to)->with('success', __('accounts.transferred'));
    }

    public function reconcile(Account $account, AccountTransaction $transaction)
    {
        abort_unless((int) $transaction->account_id === (int) $account->id, 404);

        $transaction->update(['reconciled_at' => $transaction->reconciled_at ? null : now()]);

        return back()->with('success', __('accounts.reconciled_updated'));
    }

    public function export(Account $account): StreamedResponse
    {
        $rows = $account->transactions()->orderBy('transaction_date')->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Direction', 'Amount', 'Currency', 'Rate', 'Base amount', 'Category', 'Description', 'Reconciled']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->transaction_date?->format('Y-m-d'), $row->direction, $row->amount, $row->currency,
                    $row->exchange_rate, $row->base_amount, $row->category, $row->description,
                    $row->reconciled_at ? 'yes' : 'no',
                ]);
            }

            fclose($out);
        }, 'account-'.$account->id.'-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadAttachment(Account $account, AccountTransaction $transaction)
    {
        abort_unless((int) $transaction->account_id === (int) $account->id, 404);
        abort_unless($transaction->attachment_path && Storage::disk('private')->exists($transaction->attachment_path), 404);

        return Storage::disk('private')->download(
            $transaction->attachment_path,
            $transaction->attachment_name ?: 'attachment'
        );
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:'.implode(',', Account::TYPES)],
            'currency' => ['required', 'string', 'size:3'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:60'],
            'iban' => ['nullable', 'string', 'max:60'],
            'opening_balance' => ['nullable', 'numeric'],
            'exchange_rate_to_base' => ['nullable', 'numeric', 'min:0.000001'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['currency'] = strtoupper($data['currency']);
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['exchange_rate_to_base'] = $data['exchange_rate_to_base'] ?? 1;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
