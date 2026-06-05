<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\ClientCommunicationController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectAttachmentController;
use App\Http\Controllers\Project\ProjectWorkflowController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Task\TaskCommentController;
use App\Http\Controllers\Task\TaskChecklistController;
use App\Http\Controllers\Task\TimeLogController;
use App\Http\Controllers\Task\KanbanController;
use App\Http\Controllers\Finance\QuoteController;
use App\Http\Controllers\Finance\ContractController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\ChangeRequest\ChangeRequestController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\ProjectReportController;
use App\Http\Controllers\Report\TaskReportController;
use App\Http\Controllers\Report\FinanceReportController;
use App\Http\Controllers\Report\PerformanceReportController;
use App\Http\Controllers\Portal\ClientPortalController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Settings\ServiceTypeController;
use App\Http\Controllers\Settings\ProjectStatusController;
use App\Http\Controllers\Settings\TaskStatusController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\RoleController;
use App\Http\Controllers\User\PermissionController;

// Guest
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

// Language switch
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en'])) { session(['locale' => $locale]); }
    return redirect()->back();
})->name('lang.switch');

// Public quotes
Route::get('/q/{token}', [QuoteController::class, 'publicShow'])->name('quotes.public');

// Client Portal Routes
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [ClientPortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClientPortalController::class, 'login'])->name('login.post');
    Route::get('/setup/{token}', [ClientPortalController::class, 'showSetup'])->name('setup');
    Route::post('/setup/{token}', [ClientPortalController::class, 'completeSetup'])->name('setup.post');

    Route::middleware(['auth:client'])->group(function () {
        Route::get('/dashboard', [ClientPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/projects/{project}', [ClientPortalController::class, 'projectShow'])->name('projects.show');
        Route::post('/logout', [ClientPortalController::class, 'logout'])->name('logout');
    });
});

// Authenticated
Route::middleware(['auth', 'set.locale'])->group(function () {
    Route::post('logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', fn() => redirect()->route('dashboard'))->name('index');
        Route::post('{id}/read', function ($id) { auth()->user()->notifications()->findOrFail($id)->markAsRead(); return redirect()->back(); })->name('read');
        Route::post('read-all', function () { auth()->user()->unreadNotifications->markAsRead(); return redirect()->back(); })->name('read-all');
    });

    // Clients
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('{client}', [ClientController::class, 'show'])->name('show');
        Route::get('{client}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('{client}', [ClientController::class, 'update'])->name('update');
        Route::delete('{client}', [ClientController::class, 'destroy'])->name('destroy');
        Route::post('{client}/portal-invite', [ClientController::class, 'resendPortalInvite'])->name('portal-invite');
        Route::post('{client}/communications', [ClientCommunicationController::class, 'store'])->name('communications.store');
        Route::delete('communications/{communication}', [ClientCommunicationController::class, 'destroy'])->name('communications.destroy');
    });

    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('board', [ProjectController::class, 'kanban'])->name('board');
        Route::get('create', [ProjectController::class, 'create'])->name('create');
        Route::get('{project}/handoff', [ProjectController::class, 'handoff'])->name('handoff');
        Route::post('{project}/handoff', [ProjectController::class, 'completeHandoff'])->name('handoff.complete');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::post('{project}/attachments', [ProjectAttachmentController::class, 'store'])->name('attachments.store');
        Route::delete('attachments/{attachment}', [ProjectAttachmentController::class, 'destroy'])->name('attachments.destroy');
        Route::get('{project}/workflow', [ProjectWorkflowController::class, 'show'])->name('workflow.show');
        Route::post('{project}/workflow/{step}/complete', [ProjectWorkflowController::class, 'completeStep'])->name('workflow.complete');
        Route::post('{project}/workflow/{step}/undo', [ProjectWorkflowController::class, 'undoStep'])->name('workflow.undo');
    });

    // Tasks
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('kanban', [KanbanController::class, 'index'])->name('kanban');
        Route::get('calendar', [TaskController::class, 'calendar'])->name('calendar');
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
        Route::post('{task}/time-logs', [TimeLogController::class, 'store'])->name('time-logs.store');
        Route::delete('time-logs/{log}', [TimeLogController::class, 'destroy'])->name('time-logs.destroy');
    });

    // Quotes
    Route::prefix('quotes')->name('quotes.')->group(function () {
        Route::get('/', [QuoteController::class, 'index'])->name('index');
        Route::get('create', [QuoteController::class, 'create'])->name('create');
        Route::post('/', [QuoteController::class, 'store'])->name('store');
        Route::get('{quote}/pdf', [QuoteController::class, 'pdf'])->name('pdf');
        Route::get('{quote}', [QuoteController::class, 'show'])->name('show');
        Route::get('{quote}/edit', [QuoteController::class, 'edit'])->name('edit');
        Route::put('{quote}', [QuoteController::class, 'update'])->name('update');
        Route::delete('{quote}', [QuoteController::class, 'destroy'])->name('destroy');
        Route::post('{quote}/send', [QuoteController::class, 'send'])->name('send');
        Route::post('{quote}/convert', [QuoteController::class, 'convertToProject'])->name('convert');
        Route::post('{quote}/convert-to-invoice', [QuoteController::class, 'convertToInvoice'])->name('convert-to-invoice');
    });

    // Contracts
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('create', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('{contract}/pdf', [ContractController::class, 'pdf'])->name('pdf');
        Route::get('{contract}', [ContractController::class, 'show'])->name('show');
        Route::get('{contract}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('{contract}', [ContractController::class, 'update'])->name('update');
        Route::delete('{contract}', [ContractController::class, 'destroy'])->name('destroy');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
        Route::get('{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('{invoice}/convert-to-contract', [InvoiceController::class, 'convertToContract'])->name('convert-to-contract');
    });

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
        Route::put('{payment}', [PaymentController::class, 'update'])->name('update');
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
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('projects', [ProjectReportController::class, 'index'])->name('projects');
        Route::get('tasks', [TaskReportController::class, 'index'])->name('tasks');
        Route::get('finance', [FinanceReportController::class, 'index'])->name('finance');
        Route::get('performance', [PerformanceReportController::class, 'index'])->name('performance');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->middleware('permission:manage-settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'update'])->name('update');
        Route::prefix('service-types')->name('service-types.')->group(function () {
            Route::get('/', [ServiceTypeController::class, 'index'])->name('index');
            Route::post('/', [ServiceTypeController::class, 'store'])->name('store');
            Route::put('{serviceType}', [ServiceTypeController::class, 'update'])->name('update');
            Route::delete('{serviceType}', [ServiceTypeController::class, 'destroy'])->name('destroy');
            Route::post('{serviceType}/items', [ServiceTypeController::class, 'storeItem'])->name('items.store');
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
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::delete('{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });
});
