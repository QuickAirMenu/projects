<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\BankImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BankStatementImportService
{
    public function parse(UploadedFile $file): array
    {
        $content = (string) file_get_contents($file->getRealPath());
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = array_values(array_filter(explode("\n", $content), fn ($l) => trim($l) !== ''));

        if ($lines === []) {
            return ['rows' => [], 'errors' => [__('accounts.import_empty')], 'mapping' => []];
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $parsed = array_map(fn ($line) => str_getcsv($line, $delimiter), $lines);

        $header = $this->normalizeRow($parsed[0]);
        $hasHeader = $this->looksLikeHeader($header);
        $map = $hasHeader ? $this->mapColumns($header) : ['date' => 0, 'description' => 1, 'amount' => 2];
        $dataRows = $hasHeader ? array_slice($parsed, 1) : $parsed;

        $rows = [];
        $errors = [];

        foreach ($dataRows as $index => $cells) {
            $lineNo = $index + ($hasHeader ? 2 : 1);
            $dateRaw = trim((string) ($cells[$map['date']] ?? ''));
            $description = trim((string) ($cells[$map['description']] ?? ''));

            $amount = null;
            if (isset($map['amount'])) {
                $amount = $this->parseAmount($cells[$map['amount']] ?? '');
            } elseif (isset($map['debit'], $map['credit'])) {
                $debit = $this->parseAmount($cells[$map['debit']] ?? '');
                $credit = $this->parseAmount($cells[$map['credit']] ?? '');
                $amount = ($credit ?? 0) - abs($debit ?? 0);
            }

            $date = $this->parseDate($dateRaw);

            if ($date === null || $amount === null || abs($amount) < 0.01) {
                $errors[] = __('accounts.import_row_error', ['line' => $lineNo]);

                continue;
            }

            $rows[] = [
                'date' => $date,
                'description' => $description !== '' ? $description : __('accounts.import_no_desc'),
                'amount' => round(abs($amount), 2),
                'direction' => $amount >= 0 ? 'in' : 'out',
                'line' => $lineNo,
            ];
        }

        return ['rows' => $rows, 'errors' => $errors, 'mapping' => $map, 'has_header' => $hasHeader];
    }

    public function preview(Account $account, array $rows): array
    {
        return array_map(function (array $row) use ($account) {
            $candidate = $this->findMatch($account, $row);

            $isMatch = $candidate instanceof AccountTransaction;

            return $row + [
                'matched_id' => $isMatch ? $candidate->id : null,
                'matched_label' => $isMatch
                    ? $candidate->transaction_date->format('Y-m-d').' · '.($candidate->description ?: __('accounts.cat_'.$candidate->category))
                    : null,
                'duplicate' => $candidate === false,
            ];
        }, $rows);
    }

    public function commit(Account $account, array $rows, string $fileName): BankImport
    {
        $matched = 0;
        $created = 0;
        $rate = (float) ($account->exchange_rate_to_base ?: 1);

        foreach ($rows as $row) {
            if (! empty($row['skip'])) {
                continue;
            }

            if (! empty($row['matched_id'])) {
                $tx = AccountTransaction::where('account_id', $account->id)->whereKey($row['matched_id'])->first();
                if ($tx && ! $tx->reconciled_at) {
                    $tx->update(['reconciled_at' => now()]);
                    $matched++;
                }

                continue;
            }

            AccountTransaction::create([
                'account_id' => $account->id,
                'transaction_date' => $row['date'],
                'direction' => $row['direction'],
                'amount' => $row['amount'],
                'currency' => $account->currency,
                'exchange_rate' => $rate,
                'base_amount' => round((float) $row['amount'] * $rate, 2),
                'category' => 'other',
                'description' => $row['description'],
                'reconciled_at' => now(),
                'created_by' => Auth::id(),
            ]);
            $created++;
        }

        return BankImport::create([
            'account_id' => $account->id,
            'file_name' => $fileName,
            'row_count' => count($rows),
            'matched_count' => $matched,
            'created_count' => $created,
            'created_by' => Auth::id(),
        ]);
    }

    protected function findMatch(Account $account, array $row): AccountTransaction|false|null
    {
        $date = Carbon::parse($row['date']);

        $alreadyImported = AccountTransaction::query()
            ->where('account_id', $account->id)
            ->where('direction', $row['direction'])
            ->whereDate('transaction_date', $date->toDateString())
            ->whereBetween('amount', [$row['amount'] - 0.01, $row['amount'] + 0.01])
            ->whereNotNull('reconciled_at')
            ->exists();

        if ($alreadyImported) {
            return false;
        }

        return AccountTransaction::query()
            ->where('account_id', $account->id)
            ->whereNull('reconciled_at')
            ->where('direction', $row['direction'])
            ->whereBetween('transaction_date', [$date->copy()->subDays(3)->toDateString(), $date->copy()->addDays(3)->toDateString()])
            ->whereBetween('amount', [$row['amount'] - 0.01, $row['amount'] + 0.01])
            ->get()
            ->sortBy(fn (AccountTransaction $tx) => abs($tx->transaction_date->startOfDay()->diffInDays($date->copy()->startOfDay(), false)))
            ->first();
    }

    protected function detectDelimiter(string $line): string
    {
        $candidates = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];
        arsort($candidates);

        return (string) array_key_first($candidates);
    }

    protected function normalizeRow(array $cells): array
    {
        return array_map(fn ($c) => mb_strtolower(trim((string) $c)), $cells);
    }

    protected function looksLikeHeader(array $cells): bool
    {
        $words = ['date', 'amount', 'description', 'details', 'debit', 'credit', 'reference', 'تاريخ', 'مبلغ', 'وصف', 'بيان', 'مدين', 'دائن', 'تفاصيل'];
        foreach ($cells as $cell) {
            foreach ($words as $word) {
                if ($cell !== '' && mb_strpos($cell, $word) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function mapColumns(array $header): array
    {
        $map = [];
        $aliases = [
            'date' => ['date', 'value date', 'transaction date', 'تاريخ', 'تاريخ العملية'],
            'description' => ['description', 'details', 'narrative', 'reference', 'وصف', 'بيان', 'تفاصيل', 'المرجع'],
            'amount' => ['amount', 'value', 'مبلغ', 'القيمة'],
            'debit' => ['debit', 'withdrawal', 'مدين', 'سحب'],
            'credit' => ['credit', 'deposit', 'دائن', 'إيداع'],
        ];

        foreach ($aliases as $key => $names) {
            foreach ($header as $index => $cell) {
                foreach ($names as $name) {
                    if ($cell === $name || mb_strpos($cell, $name) !== false) {
                        $map[$key] = $index;

                        continue 3;
                    }
                }
            }
        }

        if (! isset($map['date'])) {
            $map['date'] = 0;
        }
        if (! isset($map['description'])) {
            $map['description'] = 1;
        }
        if (! isset($map['amount']) && ! isset($map['debit']) && ! isset($map['credit'])) {
            $map['amount'] = 2;
        }

        return $map;
    }

    protected function parseAmount(mixed $value): ?float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $negative = str_contains($raw, '(') && str_contains($raw, ')');
        $raw = preg_replace('/[^0-9,.\-]/u', '', $raw);

        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            if (strrpos($raw, ',') > strrpos($raw, '.')) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace(',', '', $raw);
            }
        } elseif (str_contains($raw, ',')) {
            $parts = explode(',', $raw);
            $raw = (count($parts) === 2 && strlen(end($parts)) === 2)
                ? str_replace(',', '.', $raw)
                : str_replace(',', '', $raw);
        }

        if (! is_numeric($raw)) {
            return null;
        }

        $amount = (float) $raw;

        return $negative ? -abs($amount) : $amount;
    }

    protected function parseDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                // try next format
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
