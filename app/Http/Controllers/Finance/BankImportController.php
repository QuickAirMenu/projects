<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\BankStatementImportService;
use Illuminate\Http\Request;

class BankImportController extends Controller
{
    public function create(Account $account)
    {
        return view('finance.accounts.import', compact('account'));
    }

    public function preview(Request $request, Account $account, BankStatementImportService $service)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('file');
        $parsed = $service->parse($file);
        $rows = $service->preview($account, $parsed['rows']);

        $request->session()->put('bank_import_'.$account->id, [
            'file_name' => $file->getClientOriginalName(),
            'rows' => $rows,
        ]);

        return view('finance.accounts.import-preview', [
            'account' => $account,
            'rows' => $rows,
            'parseErrors' => $parsed['errors'],
            'fileName' => $file->getClientOriginalName(),
        ]);
    }

    public function confirm(Request $request, Account $account, BankStatementImportService $service)
    {
        $payload = $request->session()->get('bank_import_'.$account->id);

        if (! $payload) {
            return redirect()->route('accounts.import.create', $account)
                ->with('error', __('accounts.import_expired'));
        }

        $selected = array_map('intval', (array) $request->input('include', []));
        $rows = $payload['rows'];

        foreach ($rows as $index => $row) {
            $rows[$index]['skip'] = ! in_array($index, $selected, true) || ! empty($row['duplicate']);
        }

        $batch = $service->commit($account, $rows, $payload['file_name']);

        $request->session()->forget('bank_import_'.$account->id);

        return redirect()->route('accounts.show', $account)->with(
            'success',
            __('accounts.import_done', ['matched' => $batch->matched_count, 'created' => $batch->created_count])
        );
    }
}
