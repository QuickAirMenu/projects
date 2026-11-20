<?php

namespace App\Console\Commands;

use App\Models\ClientDocument;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SupportTicketAttachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneMissingFiles extends Command
{
    protected $signature = 'files:prune-missing {--force : Apply changes (otherwise dry-run)}';

    protected $description = 'Detect records pointing to missing private files and clean dangling references';

    public function handle(): int
    {
        $disk = Storage::disk('private');
        $force = (bool) $this->option('force');
        $rows = [];

        $payments = Payment::whereNotNull('proof_file_path')->get();
        $paymentMissing = $payments->filter(fn (Payment $p) => ! $disk->exists($p->proof_file_path));

        $expenses = Expense::whereNotNull('receipt_path')->get();
        $expenseMissing = $expenses->filter(fn (Expense $e) => ! $disk->exists($e->receipt_path));

        $tickets = SupportTicketAttachment::all();
        $ticketMissing = $tickets->filter(fn (SupportTicketAttachment $a) => ! $disk->exists($a->file_path));

        $documents = ClientDocument::all();
        $clientMissing = $documents->filter(fn (ClientDocument $d) => ! $disk->exists($d->file_path));

        $rows[] = ['payment_proofs', $payments->count(), $paymentMissing->count()];
        $rows[] = ['expense_receipts', $expenses->count(), $expenseMissing->count()];
        $rows[] = ['ticket_attachments', $tickets->count(), $ticketMissing->count()];
        $rows[] = ['client_documents', $documents->count(), $clientMissing->count()];

        $this->table(['Type', 'Total', 'Missing'], $rows);

        if (! $force) {
            $this->warn('Dry-run only. Re-run with --force to clean.');

            return self::SUCCESS;
        }

        $paymentMissing->each->update(['proof_file_path' => null, 'proof_file_name' => null]);
        $expenseMissing->each->update(['receipt_path' => null, 'receipt_name' => null]);
        $ticketMissing->each->delete();
        $clientMissing->each->delete();

        $this->info('Cleaned dangling file references.');

        return self::SUCCESS;
    }
}
