<?php

use App\Http\Controllers\Api\ApiDocsController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\Auth\AdminPasswordResetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\UserInvitationController;
use App\Http\Controllers\BulkActionController;
use App\Http\Controllers\ChangeRequest\ChangeRequestController;
use App\Http\Controllers\Client\ClientCommunicationController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\ClientDocumentController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Finance\AccountController;
use App\Http\Controllers\Finance\BankImportController;
use App\Http\Controllers\Finance\ContractController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\QuoteController;
use App\Http\Controllers\Finance\RecurringExpenseController;
use App\Http\Controllers\Finance\RecurringInvoiceController;
use App\Http\Controllers\Finance\TimeEntryController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Portal\ClientPortalController;
use App\Http\Controllers\Portal\PortalPushController;
use App\Http\Controllers\Project\ProjectAttachmentController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectWorkflowController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\Report\AdvancedReportController;
use App\Http\Controllers\Report\FinanceReportController;
use App\Http\Controllers\Report\PerformanceReportController;
use App\Http\Controllers\Report\ProjectReportController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\TaskReportController;
use App\Http\Controllers\Report\TeamPayoutController;
use App\Http\Controllers\Report\WipReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Settings\ActivityLogController;
use App\Http\Controllers\Settings\BackupController;
use App\Http\Controllers\Settings\ProjectStatusController;
use App\Http\Controllers\Settings\ServiceTypeController;
use App\Http\Controllers\Settings\TaskStatusController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\Task\KanbanController;
use App\Http\Controllers\Task\TaskChecklistController;
use App\Http\Controllers\Task\TaskCommentController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Task\TimeLogController;
use App\Http\Controllers\User\EmailValidationController;
use App\Http\Controllers\User\PermissionController;
use App\Http\Controllers\User\RoleController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\CheckSetup;
use App\Mail\SupportMail;
use App\Models\Setting;
use App\Services\InvoiceReminderService;
use App\Services\MailConfigService;
use App\Services\RecurringExpenseService;
use App\Services\RecurringInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;

// Guest
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('forgot-password', [AdminPasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [AdminPasswordResetController::class, 'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('reset-password/{token}', [AdminPasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [AdminPasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
    Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.login');
    Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:5,1')->name('two-factor.login.store');
});

// Route::get('/welcome', fn() => view('welcome'))->name('welcome'); // Disabled on subdomains

// Language switch
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('lang.switch');

// Help center
Route::get('/help', fn () => view('help'))->name('help');

// Public quotes
Route::get('/q/{token}', [QuoteController::class, 'publicShow'])->middleware('throttle:30,1')->name('quotes.public');
Route::get('/q/{token}/pdf', [QuoteController::class, 'publicPdf'])->middleware('throttle:30,1')->name('quotes.public-pdf');
Route::get('/q/{token}/approve', [QuoteController::class, 'publicApproveForm'])->middleware('throttle:30,1')->name('quotes.public-approve-form');
Route::post('/q/{token}/approve', [QuoteController::class, 'publicApprove'])->middleware('throttle:10,1')->name('quotes.public-approve');
Route::post('/q/{token}/reject', [QuoteController::class, 'publicReject'])->middleware('throttle:10,1')->name('quotes.public-reject');

// Client Portal Routes
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [ClientPortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClientPortalController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/setup/{token}', [ClientPortalController::class, 'showSetup'])->name('setup');
    Route::post('/setup/{token}', [ClientPortalController::class, 'completeSetup'])->name('setup.post');

    Route::get('/forgot-password', [ClientPortalController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [ClientPortalController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('forgot-password.post');
    Route::get('/reset-password/{token}', [ClientPortalController::class, 'showResetPassword'])->name('reset-password');
    Route::post('/reset-password/{token}', [ClientPortalController::class, 'completeResetPassword'])->name('reset-password.post');

    Route::middleware(['auth:client'])->group(function () {
        Route::get('/dashboard', [ClientPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/invoices', [ClientPortalController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{invoice}/pdf', [ClientPortalController::class, 'downloadInvoicePdf'])->name('invoices.pdf');
        Route::get('/invoices/{invoice}', [ClientPortalController::class, 'invoiceShow'])->name('invoices.show');
        Route::post('/invoices/{invoice}/receipt', [ClientPortalController::class, 'uploadReceipt'])->name('invoices.receipt');
        Route::get('/quotes', [ClientPortalController::class, 'quotes'])->name('quotes');
        Route::get('/quotes/{quote}/pdf', [ClientPortalController::class, 'downloadQuotePdf'])->name('quotes.pdf');
        Route::post('/quotes/{quote}/approve', [ClientPortalController::class, 'approveQuote'])->name('quotes.approve');
        Route::post('/quotes/{quote}/reject', [ClientPortalController::class, 'rejectQuote'])->name('quotes.reject');
        Route::get('/attachments/{attachment}/download', [ClientPortalController::class, 'downloadAttachment'])->name('attachments.download');
        Route::get('/projects/{project}', [ClientPortalController::class, 'projectShow'])->name('projects.show');
        Route::get('/payments', [ClientPortalController::class, 'payments'])->name('payments');
        Route::get('/statement', [ClientPortalController::class, 'statement'])->name('statement');
        Route::get('/statement/pdf', [ClientPortalController::class, 'statementPdf'])->name('statement.pdf');
        Route::post('/statement/email', [ClientPortalController::class, 'emailStatement'])->name('statement.email');
        Route::get('/tickets', [ClientPortalController::class, 'tickets'])->name('tickets');
        Route::post('/tickets', [ClientPortalController::class, 'storeTicket'])->name('tickets.store');
        Route::get('/tickets/attachments/{attachment}', [ClientPortalController::class, 'downloadTicketAttachment'])->name('tickets.attachment');
        Route::get('/payments/{payment}/proof', [ClientPortalController::class, 'downloadPaymentProof'])->name('payments.proof');
        Route::get('/push/key', [PortalPushController::class, 'key'])->name('push.key');
        Route::post('/push/subscribe', [PortalPushController::class, 'subscribe'])->name('push.subscribe');
        Route::delete('/push/unsubscribe', [PortalPushController::class, 'unsubscribe'])->name('push.unsubscribe');
        Route::get('/push/key', [PortalPushController::class, 'key'])->name('push.key');
        Route::post('/push/subscribe', [PortalPushController::class, 'subscribe'])->name('push.subscribe');
        Route::delete('/push/unsubscribe', [PortalPushController::class, 'unsubscribe'])->name('push.unsubscribe');
        Route::get('/notifications', [ClientPortalController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/read-all', [ClientPortalController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [ClientPortalController::class, 'markNotificationRead'])->name('notifications.read');
        Route::get('/contracts', [ClientPortalController::class, 'contracts'])->name('contracts');
        Route::get('/contracts/{contract}/pdf', [ClientPortalController::class, 'downloadContractPdf'])->name('contracts.pdf');
        Route::get('/profile', [ClientPortalController::class, 'profile'])->name('profile');
        Route::put('/profile', [ClientPortalController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [ClientPortalController::class, 'updatePassword'])->name('profile.password');
        Route::post('/logout', [ClientPortalController::class, 'logout'])->name('logout');
    });
});

// User Invitation (public - no auth required)
Route::get('/invitation/{token}', [UserInvitationController::class, 'showSetup'])->middleware('throttle:10,1')->name('invitation.setup');
Route::post('/invitation/{token}', [UserInvitationController::class, 'completeSetup'])->middleware('throttle:10,1')->name('invitation.setup.post');

// Authenticated
Route::middleware(['auth', 'set.locale'])->group(function () {
    Route::post('email/validate', [EmailValidationController::class, 'check'])->middleware('throttle:30,1')->name('email.validate');
    Route::get('push/key', [PushSubscriptionController::class, 'key'])->name('push.key');
    Route::post('push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::delete('push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

    Route::prefix('settings/api-tokens')->name('api-tokens.')->group(function () {
        Route::get('/', [ApiTokenController::class, 'index'])->name('index');
        Route::post('/', [ApiTokenController::class, 'store'])->name('store');
        Route::delete('/{token}', [ApiTokenController::class, 'destroy'])->name('destroy');
    });

    Route::post('logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/support', fn () => view('support'))->name('support');
    Route::post('/support', function (Request $request) {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $settings = Setting::whereIn('key', ['company_name', 'company_name_ar', 'company_logo'])
            ->pluck('value', 'key')
            ->toArray();

        $toEmail = $settings['company_email'] ?? 'support@airmenu.net';

        try {
            app(MailConfigService::class)->applyDynamicMailConfig();
            if (config('mail.mailers.smtp.host')) {
                $mail = new SupportMail(
                    auth()->user()->name,
                    auth()->user()->email,
                    $request->subject,
                    $request->message,
                );

                if ($request->hasFile('attachment')) {
                    $mail->attach($request->file('attachment')->getRealPath(), [
                        'as' => $request->file('attachment')->getClientOriginalName(),
                        'mime' => $request->file('attachment')->getMimeType(),
                    ]);
                }

                Mail::to($toEmail)->send($mail);
            }
        } catch (Throwable $e) {
            report($e);
        }

        return back()->with('success', __('support.message_sent'));
    })->name('support.send');

    // Profile
    Route::get('two-factor', [TwoFactorController::class, 'index'])->name('two-factor.index');
    Route::post('two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::get('count', [NotificationController::class, 'unreadCount'])->name('count');
    });

    // Global search (Cmd-K)
    Route::get('search', SearchController::class)->name('search');

    // Bulk actions
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::post('projects/delete', [BulkActionController::class, 'bulkDestroyProjects'])->name('projects.delete');
        Route::post('invoices/delete', [BulkActionController::class, 'bulkDestroyInvoices'])->name('invoices.delete');
    });

    // Clients
    Route::prefix('clients')->name('clients.')->middleware('permission:view-clients')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('{client}', [ClientController::class, 'show'])->name('show');
        Route::get('{client}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('{client}', [ClientController::class, 'update'])->name('update');
        Route::delete('{client}', [ClientController::class, 'destroy'])->name('destroy');
        Route::post('{client}/portal-invite', [ClientController::class, 'resendPortalInvite'])->name('portal-invite');
        Route::post('{client}/documents', [ClientDocumentController::class, 'store'])->name('documents.store');
        Route::get('{client}/documents/{document}/download', [ClientDocumentController::class, 'download'])->name('documents.download');
        Route::delete('{client}/documents/{document}', [ClientDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('{client}/communications', [ClientCommunicationController::class, 'store'])->name('communications.store');
        Route::delete('communications/{communication}', [ClientCommunicationController::class, 'destroy'])->name('communications.destroy');
    });

    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('board', [ProjectController::class, 'kanban'])->name('board');
        Route::get('calendar', [ProjectController::class, 'calendar'])->name('calendar');
        Route::get('calendar/data', [ProjectController::class, 'calendarData'])->name('calendar.data');
        Route::get('timeline', [ProjectController::class, 'timeline'])->name('timeline');
        Route::get('create', [ProjectController::class, 'create'])->name('create');
        Route::get('{project}/handoff', [ProjectController::class, 'handoff'])->name('handoff');
        Route::post('{project}/handoff', [ProjectController::class, 'completeHandoff'])->name('handoff.complete');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::post('{project}/attachments', [ProjectAttachmentController::class, 'store'])->name('attachments.store');
        Route::get('attachments/{attachment}/download', [ProjectAttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('attachments/{attachment}', [ProjectAttachmentController::class, 'destroy'])->name('attachments.destroy');
        Route::get('{project}/workflow', [ProjectWorkflowController::class, 'show'])->name('workflow.show');
        Route::post('{project}/workflow/{step}/complete', [ProjectWorkflowController::class, 'completeStep'])->name('workflow.complete');
        Route::post('{project}/workflow/{step}/undo', [ProjectWorkflowController::class, 'undoStep'])->name('workflow.undo');
        Route::post('{project}/archive', [ProjectController::class, 'archive'])->name('archive');
        Route::post('{project}/unarchive', [ProjectController::class, 'unarchive'])->name('unarchive');
    });

    // Tasks
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('kanban', [KanbanController::class, 'index'])->name('kanban');
        Route::get('calendar', [TaskController::class, 'calendar'])->name('calendar');
        Route::get('calendar/data', [TaskController::class, 'calendarData'])->name('calendar.data');
        Route::get('timeline', [TaskController::class, 'timeline'])->name('timeline');
        Route::get('create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('{task}', [TaskController::class, 'show'])->name('show');
        Route::get('{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::post('{task}/status', [KanbanController::class, 'updateStatus'])->name('status.update');
        Route::post('{task}/comments', [TaskCommentController::class, 'store'])->name('comments.store');
        Route::delete('comments/{comment}', [TaskCommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('{task}/checklist', [TaskChecklistController::class, 'store'])->name('checklist.store');
        Route::put('checklist/{item}', [TaskChecklistController::class, 'update'])->name('checklist.update');
        Route::delete('checklist/{item}', [TaskChecklistController::class, 'destroy'])->name('checklist.destroy');
        Route::get('{task}/cost/pay', [TaskController::class, 'payoutForm'])->name('cost.payout');
        Route::post('{task}/cost/pay', [TaskController::class, 'payCost'])->name('cost.pay');
        Route::post('{task}/cost/unpay', [TaskController::class, 'unpayCost'])->name('cost.unpay');
        Route::post('{task}/cost/pay', [TaskController::class, 'payCost'])->name('cost.pay');
        Route::post('{task}/cost/unpay', [TaskController::class, 'unpayCost'])->name('cost.unpay');
        Route::post('{task}/time-logs', [TimeLogController::class, 'store'])->name('time-logs.store');
        Route::delete('time-logs/{log}', [TimeLogController::class, 'destroy'])->name('time-logs.destroy');
    });

    // Quotes
    Route::prefix('quotes')->name('quotes.')->middleware('permission:view-quotes')->group(function () {
        Route::get('/', [QuoteController::class, 'index'])->name('index');
        Route::get('create', [QuoteController::class, 'create'])->name('create');
        Route::post('/', [QuoteController::class, 'store'])->name('store');
        Route::get('{quote}/pdf', [QuoteController::class, 'pdf'])->withTrashed()->name('pdf');
        Route::get('{quote}', [QuoteController::class, 'show'])->withTrashed()->name('show');
        Route::get('{quote}/items', [QuoteController::class, 'itemsJson'])->name('items');
        Route::post('{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('duplicate');
        Route::get('{quote}/edit', [QuoteController::class, 'edit'])->name('edit');
        Route::put('{quote}', [QuoteController::class, 'update'])->name('update');
        Route::post('{quote}/restore', [QuoteController::class, 'restore'])->withTrashed()->name('restore');
        Route::delete('{quote}/force', [QuoteController::class, 'forceDestroy'])->withTrashed()->name('force-delete');
        Route::delete('{quote}', [QuoteController::class, 'destroy'])->name('destroy');
        Route::post('{quote}/send', [QuoteController::class, 'send'])->name('send');
        Route::post('{quote}/send-whatsapp', [QuoteController::class, 'sendWhatsApp'])->name('send-whatsapp');
        Route::post('{quote}/approve', [QuoteController::class, 'approve'])->name('approve');
        Route::post('{quote}/redraft', [QuoteController::class, 'redraft'])->name('redraft');
        Route::post('{quote}/convert', [QuoteController::class, 'convertToProject'])->name('convert');
        Route::post('{quote}/convert-to-invoice', [QuoteController::class, 'convertToInvoice'])->name('convert-to-invoice');
    });

    // Contracts
    Route::prefix('contracts')->name('contracts.')->middleware('permission:view-contracts')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('create', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('{contract}/pdf', [ContractController::class, 'pdf'])->name('pdf');
        Route::get('{contract}/download', [ContractController::class, 'downloadFile'])->name('download');
        Route::get('{contract}', [ContractController::class, 'show'])->name('show');
        Route::get('{contract}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('{contract}', [ContractController::class, 'update'])->name('update');
        Route::post('{contract}/restore', [ContractController::class, 'restore'])->withTrashed()->name('restore');
        Route::delete('{contract}', [ContractController::class, 'destroy'])->name('destroy');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware('permission:view-invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('create', [InvoiceController::class, 'create'])->name('create');
        Route::post('remind-overdue', [InvoiceController::class, 'remindOverdue'])->name('remind-overdue');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('{invoice}/pdf', [InvoiceController::class, 'pdf'])->withTrashed()->name('pdf');
        Route::get('{invoice}', [InvoiceController::class, 'show'])->withTrashed()->name('show');
        Route::get('{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::post('{invoice}/restore', [InvoiceController::class, 'restore'])->withTrashed()->name('restore');
        Route::delete('{invoice}/force', [InvoiceController::class, 'forceDestroy'])->withTrashed()->name('force-delete');
        Route::delete('{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('{invoice}/remind', [InvoiceController::class, 'remind'])->name('remind');
        Route::post('{invoice}/convert-to-contract', [InvoiceController::class, 'convertToContract'])->name('convert-to-contract');
    });

    // Payments
    Route::prefix('payments')->name('payments.')->middleware('permission:view-payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
        Route::get('{payment}/download-proof', [PaymentController::class, 'downloadProof'])->name('download-proof');
        Route::get('{payment}/view-proof', [PaymentController::class, 'viewProof'])->name('view-proof');
        Route::put('{payment}', [PaymentController::class, 'update'])->name('update');
        Route::post('{payment}/restore', [PaymentController::class, 'restore'])->withTrashed()->name('restore');
        Route::post('{payment}/approve', [PaymentController::class, 'approve'])->name('approve');
        Route::post('{payment}/reject', [PaymentController::class, 'reject'])->name('reject');
        Route::delete('{payment}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    // Change Requests
    Route::prefix('change-requests')->name('change-requests.')->group(function () {
        Route::get('/', [ChangeRequestController::class, 'index'])->name('index');
        Route::get('create', [ChangeRequestController::class, 'create'])->name('create');
        Route::post('/', [ChangeRequestController::class, 'store'])->name('store');
        Route::get('{changeRequest}', [ChangeRequestController::class, 'show'])->name('show');
        Route::post('{changeRequest}/approve', [ChangeRequestController::class, 'approve'])->name('approve');
        Route::post('{changeRequest}/reject', [ChangeRequestController::class, 'reject'])->name('reject');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:view-reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('projects', [ProjectReportController::class, 'index'])->name('projects');
        Route::get('tasks', [TaskReportController::class, 'index'])->name('tasks');
        Route::get('finance', [FinanceReportController::class, 'index'])->name('finance');
        Route::get('finance/export', [FinanceReportController::class, 'export'])->name('finance.export');
        Route::get('aging', [AdvancedReportController::class, 'aging'])->name('aging');
        Route::get('aging/export', [AdvancedReportController::class, 'agingExport'])->name('aging.export');
        Route::get('profitability/export', [AdvancedReportController::class, 'profitabilityExport'])->name('profitability.export');
        Route::post('team-payouts/pay', [TeamPayoutController::class, 'batchPay'])->middleware('permission:create-payments')->name('team-payouts.pay');
        Route::get('team-payouts/{user}/statement/pdf', [TeamPayoutController::class, 'statementPdf'])->name('team-payouts.statement');
        Route::get('team-payouts', [TeamPayoutController::class, 'index'])->name('team-payouts');
        Route::get('wip', [WipReportController::class, 'index'])->name('wip');
        Route::get('wip/export', [WipReportController::class, 'export'])->name('wip.export');
        Route::get('profitability', [AdvancedReportController::class, 'profitability'])->name('profitability');
        Route::get('performance', [PerformanceReportController::class, 'index'])->name('performance');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->middleware('permission:manage-settings')->group(function () {
        Route::get('backups', [BackupController::class, 'index'])->name('backups');
        Route::get('audit-log', [ActivityLogController::class, 'index'])->name('audit-log');
        Route::post('mail-test', [SettingController::class, 'testMail'])->name('mail-test');
        Route::post('backups/run', [BackupController::class, 'run'])->name('backups.run');
        Route::get('backups/{file}', [BackupController::class, 'download'])->where('file', '[A-Za-z0-9_.-]+')->name('backups.download');
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'update'])->name('update');
        Route::prefix('service-types')->name('service-types.')->group(function () {
            Route::get('/', [ServiceTypeController::class, 'index'])->name('index');
            Route::post('/', [ServiceTypeController::class, 'store'])->name('store');
            Route::put('{serviceType}', [ServiceTypeController::class, 'update'])->name('update');
            Route::delete('{serviceType}', [ServiceTypeController::class, 'destroy'])->name('destroy');
            Route::post('{serviceType}/items', [ServiceTypeController::class, 'storeItem'])->name('items.store');
            Route::put('{serviceType}/items/{item}', [ServiceTypeController::class, 'updateItem'])->name('items.update');
            Route::delete('{serviceType}/items/{item}', [ServiceTypeController::class, 'destroyItem'])->name('items.destroy');
        });
        Route::prefix('project-statuses')->name('project-statuses.')->group(function () {
            Route::get('/', [ProjectStatusController::class, 'index'])->name('index');
            Route::post('/', [ProjectStatusController::class, 'store'])->name('store');
            Route::put('{projectStatus}', [ProjectStatusController::class, 'update'])->name('update');
            Route::delete('{projectStatus}', [ProjectStatusController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('task-statuses')->name('task-statuses.')->group(function () {
            Route::get('/', [TaskStatusController::class, 'index'])->name('index');
            Route::post('/', [TaskStatusController::class, 'store'])->name('store');
            Route::put('{taskStatus}', [TaskStatusController::class, 'update'])->name('update');
            Route::delete('{taskStatus}', [TaskStatusController::class, 'destroy'])->name('destroy');
        });
    });

    // Users
    Route::prefix('users')->name('users.')->middleware('permission:manage-users')->group(function () {
        Route::post('validate-email', [EmailValidationController::class, 'check'])->name('validate-email');
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('{user}/invite', [UserController::class, 'invite'])->name('invite');
    });
    Route::prefix('roles')->name('roles.')->middleware('permission:manage-roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('permissions')->name('permissions.')->middleware('permission:manage-roles')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::delete('{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    // Imports
    Route::prefix('imports')->name('imports.')->group(function () {
        Route::get('/', [ImportController::class, 'showForm'])->name('index');
        Route::get('clients/template', [ImportController::class, 'downloadClientTemplate'])->name('clients.template');
        Route::get('projects/template', [ImportController::class, 'downloadProjectTemplate'])->name('projects.template');
        Route::post('clients', [ImportController::class, 'importClients'])->name('clients');
        Route::post('projects', [ImportController::class, 'importProjects'])->name('projects');
    });

    // Recurring invoices
    Route::resource('recurring-invoices', RecurringInvoiceController::class)
        ->except(['show'])
        ->parameters(['recurring-invoices' => 'recurringInvoice']);
    Route::post('recurring-invoices/{recurringInvoice}/run', [RecurringInvoiceController::class, 'runNow'])
        ->name('recurring-invoices.run');

    // Timesheets
    Route::prefix('time-entries')->name('time-entries.')->middleware('permission:view-projects')->group(function () {
        Route::get('/', [TimeEntryController::class, 'index'])->name('index');
        Route::get('create', [TimeEntryController::class, 'create'])->name('create');
        Route::post('/', [TimeEntryController::class, 'store'])->name('store');
        Route::get('bill', [TimeEntryController::class, 'billPreview'])->name('bill.preview');
        Route::post('bill', [TimeEntryController::class, 'bill'])->name('bill.store');
        Route::get('{timeEntry}/edit', [TimeEntryController::class, 'edit'])->name('edit');
        Route::put('{timeEntry}', [TimeEntryController::class, 'update'])->name('update');
        Route::delete('{timeEntry}', [TimeEntryController::class, 'destroy'])->name('destroy');
    });

    // Expenses
    Route::prefix('expenses')->name('expenses.')->middleware('permission:view-expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('create', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::get('bill', [ExpenseController::class, 'billPreview'])->name('bill.preview');
        Route::post('bill', [ExpenseController::class, 'bill'])->name('bill.store');
        Route::get('{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::get('{expense}/receipt', [ExpenseController::class, 'downloadReceipt'])->name('receipt');
        Route::post('{expense}/approve', [ExpenseController::class, 'approve'])->middleware('permission:approve-expenses')->name('approve');
        Route::post('{expense}/reject', [ExpenseController::class, 'reject'])->middleware('permission:approve-expenses')->name('reject');
    });

    // Recurring expenses
    Route::prefix('recurring-expenses')->name('recurring-expenses.')->middleware('permission:view-expenses')->group(function () {
        Route::get('/', [RecurringExpenseController::class, 'index'])->name('index');
        Route::get('create', [RecurringExpenseController::class, 'create'])->name('create');
        Route::post('/', [RecurringExpenseController::class, 'store'])->name('store');
        Route::get('{recurringExpense}/edit', [RecurringExpenseController::class, 'edit'])->name('edit');
        Route::put('{recurringExpense}', [RecurringExpenseController::class, 'update'])->name('update');
        Route::delete('{recurringExpense}', [RecurringExpenseController::class, 'destroy'])->name('destroy');
        Route::post('{recurringExpense}/run', [RecurringExpenseController::class, 'runNow'])->name('run');
    });

    // Treasury accounts
    Route::prefix('accounts')->name('accounts.')->middleware('permission:view-payments')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('create', [AccountController::class, 'create'])->name('create');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::get('transfer', [AccountController::class, 'transferForm'])->name('transfer.form');
        Route::post('transfer', [AccountController::class, 'transfer'])->name('transfer.store');
        Route::get('{account}', [AccountController::class, 'show'])->name('show');
        Route::get('{account}/edit', [AccountController::class, 'edit'])->name('edit');
        Route::put('{account}', [AccountController::class, 'update'])->name('update');
        Route::delete('{account}', [AccountController::class, 'destroy'])->name('destroy');
        Route::post('{account}/transactions', [AccountController::class, 'storeTransaction'])->name('transactions.store');
        Route::post('{account}/transactions/{transaction}/reconcile', [AccountController::class, 'reconcile'])->name('reconcile');
        Route::get('{account}/transactions/{transaction}/attachment', [AccountController::class, 'downloadAttachment'])->name('attachment');
        Route::get('{account}/import', [BankImportController::class, 'create'])->name('import.create');
        Route::post('{account}/import/preview', [BankImportController::class, 'preview'])->name('import.preview');
        Route::post('{account}/import/confirm', [BankImportController::class, 'confirm'])->name('import.confirm');
        Route::get('{account}/statement/pdf', [AccountController::class, 'statementPdf'])->name('statement');
        Route::get('{account}/export', [AccountController::class, 'export'])->name('export');
    });

    // Support tickets
    Route::prefix('support-tickets')->name('support-tickets.')->middleware('permission:manage-tickets')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::post('{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
        Route::post('{supportTicket}/close', [SupportTicketController::class, 'close'])->name('close');
        Route::get('attachments/{attachment}', [SupportTicketController::class, 'downloadAttachment'])->name('attachment');
    });
    // Exports
    Route::prefix('exports')->name('exports.')->middleware('permission:view-reports')->group(function () {
        Route::get('projects', [ExportController::class, 'projects'])->name('projects');
        Route::get('tasks', [ExportController::class, 'tasks'])->name('tasks');
        Route::get('clients', [ExportController::class, 'clients'])->name('clients');
        Route::get('invoices', [ExportController::class, 'invoices'])->name('invoices');
        Route::get('quotes', [ExportController::class, 'quotes'])->name('quotes');
        Route::get('payments', [ExportController::class, 'payments'])->name('payments');
        Route::get('contracts', [ExportController::class, 'contracts'])->name('contracts');
    });
});

Route::get('/manifest.webmanifest', function () {
    $name = Setting::where('key', 'company_name_ar')->value('value')
        ?: (Setting::where('key', 'company_name')->value('value') ?: config('app.name'));

    return response()->json([
        'name' => $name,
        'short_name' => mb_substr($name, 0, 12),
        'description' => 'iProjects ERP',
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => 'portrait',
        'background_color' => '#ffffff',
        'theme_color' => '#4F46E5',
        'dir' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
        'lang' => app()->getLocale(),
        'icons' => [
            ['src' => asset('icons/ip-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => asset('icons/ip-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ], 200, ['Content-Type' => 'application/manifest+json']);
})->name('manifest');
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'env' => config('app.env'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Setup Wizard Routes
Route::get('/setup/{token}', [SetupController::class, 'show'])->name('setup');
Route::post('/setup/{token}', [SetupController::class, 'store'])->name('setup.post');
Route::get('/setup-pending', [SetupController::class, 'pending'])->name('setup.pending');
// Secured backup trigger (token stored in .env, never in repo)
Route::post('/internal/backup', function (Request $request) {
    $expected = (string) config('internal.token', '');
    $given = (string) $request->input('token', '');

    abort_if($expected === '' || ! hash_equals($expected, $given), 403);

    $process = new Process(['bash', '/home/u227212075/backups/iprojects/backup-all.sh']);
    $process->setTimeout(280);
    $process->run();

    return response($process->getOutput().$process->getErrorOutput(), 200)->header('Content-Type', 'text/plain');
})->withoutMiddleware([CheckSetup::class])->middleware('throttle:5,1');

Route::get('api/docs', [ApiDocsController::class, 'ui'])->name('api.docs');
Route::get('api/v1/openapi.json', [ApiDocsController::class, 'spec'])->name('api.openapi');

Route::post('/internal/recurring', function (Request $request) {
    $expected = (string) config('internal.token', '');
    $given = (string) $request->input('token', '');

    abort_if($expected === '' || ! hash_equals($expected, $given), 403);

    [$generated, $failed] = app(RecurringInvoiceService::class)->processDue();

    return response("generated={$generated} failed={$failed}", 200)->header('Content-Type', 'text/plain');
})->middleware('throttle:5,1');

Route::post('/csp-report', function (Request $request) {
    $report = $request->json('csp-report', $request->all());

    Log::warning('csp-report', [
        'document' => $report['document-uri'] ?? null,
        'directive' => $report['effective-directive'] ?? ($report['violated-directive'] ?? null),
        'blocked' => $report['blocked-uri'] ?? null,
        'ip' => $request->ip(),
    ]);

    return response()->noContent();
})->withoutMiddleware([CheckSetup::class])->middleware('throttle:60,1');

Route::post('/internal/reminders', function (Request $request) {
    $expected = (string) config('internal.token', '');
    $given = (string) $request->input('token', '');

    abort_if($expected === '' || ! hash_equals($expected, $given), 403);

    try {
        [$sent, $skipped, $failed] = app(InvoiceReminderService::class)->sendDue();
    } catch (Throwable $e) {
        return response($e->getMessage(), 422)->header('Content-Type', 'text/plain');
    }

    return response("sent={$sent} skipped={$skipped} failed={$failed}", 200)->header('Content-Type', 'text/plain');
})->middleware('throttle:5,1');

Route::post('/internal/budget-alerts', function (Request $request) {
    $expected = (string) config('internal.token', '');
    $given = (string) $request->input('token', '');

    abort_if($expected === '' || ! hash_equals($expected, $given), 403);

    $options = [];

    if ($request->filled('threshold')) {
        $options['--threshold'] = max(1, min(100, (int) $request->input('threshold')));
    }

    if ($request->filled('days')) {
        $options['--days'] = max(1, min(365, (int) $request->input('days')));
    }

    Artisan::call('projects:budget-alerts', $options);

    return response(trim(Artisan::output()), 200)->header('Content-Type', 'text/plain');
})->middleware('throttle:5,1');

Route::post('/internal/recurring-expenses', function (Request $request) {
    $expected = (string) config('internal.token', '');
    $given = (string) $request->input('token', '');

    abort_if($expected === '' || ! hash_equals($expected, $given), 403);

    [$generated, $failed] = app(RecurringExpenseService::class)->processDue();

    return response("generated={$generated} failed={$failed}", 200)->header('Content-Type', 'text/plain');
})->middleware('throttle:5,1');
