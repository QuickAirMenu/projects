<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // mPDF needs a larger PCRE backtrack limit for large HTML documents.
        @ini_set('pcre.backtrack_limit', '100000000');

        // Register Observers
        Project::observe(ProjectObserver::class);
        Task::observe(TaskObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);

        // Custom Blade directives
        Blade::directive('money', function ($amount) {
            return "<?php echo number_format($amount, 2) . ' ' . __('messages.currency'); ?>";
        });

        Blade::directive('date', function ($date) {
            return "<?php echo $date ? \Carbon\Carbon::parse($date)->format('Y-m-d') : '-'; ?>";
        });

        try {
            if (Schema::hasTable('settings')) {
                app(MailConfigService::class)->applyDynamicMailConfig();
            }
        } catch (\Throwable) {
            // Ignore during early boot or if DB is unavailable.
        }
    }
}
