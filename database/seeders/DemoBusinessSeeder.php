<?php

namespace Database\Seeders;

use App\Models\ChangeRequest;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\ServiceType;
use App\Models\ServiceTypeItem;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $this->dedupeSeedData();

        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'super-admin'))->first()
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $finance = User::whereHas('roles', fn($q) => $q->where('name', 'finance'))->first() ?? $admin;
        $member = User::whereHas('roles', fn($q) => $q->where('name', 'team-member'))->first() ?? $admin;

        $clients = Client::orderBy('id')->take(4)->get();
        $projects = Project::with(['client', 'tasks.status'])->orderBy('id')->take(4)->get();
        $serviceTypes = ServiceType::orderBy('sort_order')->get();

        if ($clients->isEmpty() || $projects->isEmpty() || $serviceTypes->isEmpty()) {
            return;
        }

        $this->seedQuotes($clients, $serviceTypes, $admin);
        $this->seedInvoices($projects, $admin, $finance);
        $this->seedContracts($admin);
        $this->seedChangeRequests($projects, $admin);
        $this->seedTaskExtras($projects, $member, $finance);
        $this->seedServiceTypeItems($serviceTypes);
    }

    protected function dedupeSeedData(): void
    {
        $this->dedupeBy('App\\Models\\User', ['email']);
        $this->dedupeBy('App\\Models\\Client', ['email']);
        $this->dedupeBy('App\\Models\\ServiceType', ['name_en']);
        $this->dedupeBy('App\\Models\\ProjectStatus', ['slug']);
        $this->dedupeBy('App\\Models\\TaskStatus', ['slug']);
        $this->dedupeBy('App\\Models\\Project', ['name']);
        $this->dedupeBy('App\\Models\\Task', ['project_id', 'title']);
        $this->dedupeBy('App\\Models\\Quote', ['subject']);
        $this->dedupeBy('App\\Models\\Invoice', ['project_id', 'invoice_type']);
        $this->dedupeBy('App\\Models\\Contract', ['invoice_id']);
        $this->dedupeBy('App\\Models\\ChangeRequest', ['project_id', 'title']);
        $this->dedupeBy('App\\Models\\TaskChecklist', ['task_id', 'item']);
        $this->dedupeBy('App\\Models\\TaskComment', ['task_id', 'user_id', 'comment']);
        $this->dedupeBy('App\\Models\\TimeLog', ['task_id', 'user_id', 'logged_date', 'hours', 'description']);
        $this->dedupeBy('App\\Models\\InvoiceItem', ['invoice_id', 'sort_order']);
        $this->dedupeBy('App\\Models\\QuoteItem', ['quote_id', 'sort_order']);
        $this->dedupeBy('App\\Models\\ServiceTypeItem', ['service_type_id', 'description']);
        $this->dedupeBy('App\\Models\\Payment', ['invoice_id', 'payment_number']);
    }

    protected function dedupeBy(string $modelClass, array $columns): void
    {
        $query = $modelClass::query();

        foreach ($columns as $column) {
            $query->whereNotNull($column)->where($column, '!=', '');
        }

        $rows = $query->orderBy('id')->get();
        $grouped = $rows->groupBy(fn($row) => implode('|', array_map(fn($column) => (string) $row->{$column}, $columns)));

        foreach ($grouped as $group) {
            $group->skip(1)->each(function ($row) {
                if (method_exists($row, 'forceDelete')) {
                    $row->forceDelete();
                    return;
                }

                $row->delete();
            });
        }
    }

    protected function seedQuotes($clients, $serviceTypes, ?User $creator): void
    {
        $quoteRows = [
            [
                'subject' => 'عرض هوية بصرية - مجموعة الأحمدي',
                'description' => 'عرض تفصيلي لتصميم الهوية البصرية والتطبيقات الأساسية.',
                'client_index' => 0,
                'status' => 'approved',
                'tax_percentage' => 15,
                'discount_percentage' => 5,
                'items' => [
                    ['description' => 'تصميم الشعار', 'quantity' => 1, 'unit_price' => 4500, 'service_type_index' => 0],
                    ['description' => 'دليل الهوية', 'quantity' => 1, 'unit_price' => 3500, 'service_type_index' => 0],
                ],
            ],
            [
                'subject' => 'عرض موقع إلكتروني - مطاعم سارة',
                'description' => 'عرض لتطوير موقع مع منيو رقمي ونظام طلبات.',
                'client_index' => 1,
                'status' => 'sent',
                'tax_percentage' => 15,
                'discount_percentage' => 0,
                'items' => [
                    ['description' => 'UI/UX Design', 'quantity' => 1, 'unit_price' => 7000, 'service_type_index' => 1],
                    ['description' => 'Frontend & Backend', 'quantity' => 1, 'unit_price' => 14000, 'service_type_index' => 5],
                ],
            ],
            [
                'subject' => 'عرض إدارة سوشيال ميديا - المطيري',
                'description' => 'خطة محتوى شهرية وإدارة الحسابات.',
                'client_index' => 2,
                'status' => 'draft',
                'tax_percentage' => 15,
                'discount_percentage' => 0,
                'items' => [
                    ['description' => 'خطة محتوى شهرية', 'quantity' => 1, 'unit_price' => 3000, 'service_type_index' => 2],
                    ['description' => 'تصاميم أسبوعية', 'quantity' => 4, 'unit_price' => 1250, 'service_type_index' => 4],
                ],
            ],
            [
                'subject' => 'عرض حملة رمضان - ستوديو نورة',
                'description' => 'إنتاج وتنفيذ حملة تسويقية كاملة.',
                'client_index' => 3,
                'status' => 'approved',
                'tax_percentage' => 15,
                'discount_percentage' => 10,
                'items' => [
                    ['description' => 'Strategy', 'quantity' => 1, 'unit_price' => 2500, 'service_type_index' => 9],
                    ['description' => 'Design Pack', 'quantity' => 1, 'unit_price' => 5500, 'service_type_index' => 4],
                ],
            ],
        ];

        foreach ($quoteRows as $row) {
            $client = $clients[$row['client_index']] ?? $clients->first();
            $quote = Quote::updateOrCreate(
                ['subject' => $row['subject']],
                [
                    'client_id' => $client->id,
                    'subject' => $row['subject'],
                    'description' => $row['description'],
                    'tax_percentage' => $row['tax_percentage'],
                    'discount_percentage' => $row['discount_percentage'],
                    'status' => $row['status'],
                    'valid_until' => now()->addDays(30),
                    'notes' => 'Demo data',
                    'terms_and_conditions' => 'Demo terms',
                    'created_by' => $creator?->id ?? 1,
                ]
            );

            $quote->items()->delete();
            $subtotal = 0;

            foreach ($row['items'] as $order => $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'service_type_id' => $serviceTypes[$item['service_type_index']]?->id ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                    'sort_order' => $order,
                ]);
            }

            $taxAmount = round($subtotal * ($row['tax_percentage'] / 100), 2);
            $discountAmount = round($subtotal * ($row['discount_percentage'] / 100), 2);
            $quote->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $subtotal + $taxAmount - $discountAmount,
            ]);
        }
    }

    protected function seedInvoices($projects, ?User $creator, ?User $receiver): void
    {
        $approvedQuotes = Quote::where('status', 'approved')->get()->keyBy('client_id');

        foreach ($projects as $index => $project) {
            $invoiceType = $index === 0 ? 'final' : ($index === 1 ? 'partial' : 'initial');
            $linkedQuote = $approvedQuotes[$project->client_id] ?? null;
            $invoice = Invoice::updateOrCreate(
                ['project_id' => $project->id, 'invoice_type' => $invoiceType],
                [
                    'client_id' => $project->client_id,
                    'quote_id' => $linkedQuote?->id,
                    'invoice_type' => $invoiceType,
                    'invoice_date' => now()->subDays(10 + $index * 3)->toDateString(),
                    'due_date' => now()->addDays(7 - $index * 2)->toDateString(),
                    'subtotal' => 0,
                    'tax_percentage' => 15,
                    'tax_amount' => 0,
                    'discount_amount' => $index === 0 ? 500 : 0,
                    'total' => 0,
                    'paid_amount' => 0,
                    'remaining_amount' => 0,
                    'payment_status' => $index === 0 ? 'paid' : ($index === 1 ? 'partially_paid' : 'unpaid'),
                    'notes' => 'Demo invoice',
                    'terms' => 'Due upon receipt',
                    'created_by' => $creator?->id ?? 1,
                ]
            );

            $invoice->items()->delete();

            $items = [
                ['description' => 'Service package', 'quantity' => 1, 'unit_price' => (float) $project->budget * 0.5],
                ['description' => 'Support & revisions', 'quantity' => 1, 'unit_price' => (float) $project->budget * 0.25],
            ];

            $subtotal = 0;
            foreach ($items as $order => $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                    'sort_order' => $order,
                ]);
            }

            $taxAmount = round($subtotal * 0.15, 2);
            $discountAmount = (float) $invoice->discount_amount;
            $total = $subtotal + $taxAmount - $discountAmount;
            $existingPaid = (float) $invoice->payments()->sum('amount');

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => $existingPaid,
                'remaining_amount' => max(0, $total - $existingPaid),
                'payment_status' => $existingPaid >= $total ? 'paid' : ($existingPaid > 0 ? 'partially_paid' : 'unpaid'),
            ]);

            if ($existingPaid > 0) {
                continue;
            }

            $paidAmount = $invoiceType === 'final' ? $total : ($invoiceType === 'partial' ? round($total * 0.4, 2) : 0);

            if ($paidAmount > 0) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'payment_date' => now()->subDays(2)->toDateString(),
                    'payment_method' => 'bank_transfer',
                    'transaction_reference' => 'DEMO-' . $invoice->id,
                    'notes' => 'Demo payment',
                    'received_by' => $receiver?->id ?? $creator?->id ?? 1,
                ]);
            }
        }
    }

    protected function seedContracts(?User $creator): void
    {
        $invoices = Invoice::with('project')->orderBy('id')->take(3)->get();

        if ($invoices->isEmpty()) {
            $projects = Project::with('client')->orderBy('id')->take(3)->get();

            foreach ($projects as $index => $project) {
                Contract::updateOrCreate(
                    ['project_id' => $project->id, 'invoice_id' => null],
                    [
                        'project_id' => $project->id,
                        'client_id' => $project->client_id,
                        'invoice_id' => null,
                        'title' => 'عقد خدمة مرتبط بالمشروع ' . $project->name,
                        'description' => 'عقد تجريبي يوضح الربط بين المشروع والعقد.',
                        'start_date' => now()->subDays(3 + $index)->toDateString(),
                        'end_date' => now()->addMonths(2 + $index)->toDateString(),
                        'contract_value' => $project->budget,
                        'payment_terms' => '50% مقدم و50% عند التسليم',
                        'deliverables' => 'تنفيذ وتسليم كامل حسب نطاق المشروع',
                        'terms_and_conditions' => 'Demo contract terms',
                        'signed_by_client' => $index === 0,
                        'signed_at' => $index === 0 ? now()->subDay() : null,
                        'status' => $index === 0 ? 'active' : 'draft',
                        'created_by' => $creator?->id ?? 1,
                    ]
                );
            }

            return;
        }

        foreach ($invoices as $index => $invoice) {
            Contract::updateOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'project_id' => $invoice->project_id,
                    'client_id' => $invoice->client_id,
                    'invoice_id' => $invoice->id,
                    'title' => 'عقد خدمة مرتبط بالفاتورة ' . $invoice->invoice_number,
                    'description' => 'عقد تجريبي يوضح الربط بين الفاتورة والعقد.',
                    'start_date' => now()->subDays(3 + $index)->toDateString(),
                    'end_date' => now()->addMonths(2 + $index)->toDateString(),
                    'contract_value' => $invoice->total,
                    'payment_terms' => '50% مقدم و50% عند التسليم',
                    'deliverables' => 'تنفيذ وتسليم كامل حسب نطاق المشروع',
                    'terms_and_conditions' => 'Demo contract terms',
                    'signed_by_client' => $index === 0,
                    'signed_at' => $index === 0 ? now()->subDay() : null,
                    'status' => $index === 0 ? 'active' : 'draft',
                    'created_by' => $creator?->id ?? 1,
                ]
            );
        }
    }

    protected function seedChangeRequests($projects, ?User $creator): void
    {
        $rows = [
            ['title' => 'إضافة صفحة عروض خاصة', 'project_index' => 0, 'requested_by' => 'client', 'scope_type' => 'out_of_scope', 'priority' => 'high', 'cost' => 1500, 'hours' => 8],
            ['title' => 'تعديل واجهة الحجز', 'project_index' => 1, 'requested_by' => 'client', 'scope_type' => 'out_of_scope', 'priority' => 'medium', 'cost' => 2200, 'hours' => 12],
            ['title' => 'تحديث هوية المنشورات', 'project_index' => 2, 'requested_by' => 'internal', 'scope_type' => 'in_scope', 'priority' => 'low', 'cost' => 0, 'hours' => 4],
            ['title' => 'تسريع اعتماد المحتوى', 'project_index' => 3, 'requested_by' => 'client', 'scope_type' => 'in_scope', 'priority' => 'medium', 'cost' => 0, 'hours' => 3],
        ];

        foreach ($rows as $row) {
            $project = $projects[$row['project_index']] ?? $projects->first();
            ChangeRequest::updateOrCreate(
                ['project_id' => $project->id, 'title' => $row['title']],
                [
                    'project_id' => $project->id,
                    'requested_by' => $row['requested_by'],
                    'title' => $row['title'],
                    'description' => 'Demo change request for dashboard data.',
                    'scope_type' => $row['scope_type'],
                    'impact_analysis' => 'Demo impact analysis',
                    'additional_cost' => $row['cost'],
                    'additional_hours' => $row['hours'],
                    'status' => 'pending',
                    'priority' => $row['priority'],
                    'created_by' => $creator?->id ?? 1,
                ]
            );
        }
    }

    protected function seedTaskExtras($projects, ?User $member, ?User $finance): void
    {
        foreach ($projects as $project) {
            $tasks = $project->tasks()->whereNull('parent_task_id')->orderBy('id')->take(3)->get();

            foreach ($tasks as $task) {
                $checklistItems = [
                    'راجع المتطلبات',
                    'جهز النسخة الأولى',
                    'أرسل للمراجعة',
                ];

                foreach ($checklistItems as $order => $item) {
                    TaskChecklist::firstOrCreate(
                        ['task_id' => $task->id, 'item' => $item],
                        ['is_completed' => $order === 0, 'completed_by' => $member?->id, 'completed_at' => $order === 0 ? now() : null, 'sort_order' => $order]
                    );
                }

                TimeLog::firstOrCreate(
                    [
                        'task_id' => $task->id,
                        'user_id' => $member?->id ?? 1,
                        'logged_date' => now()->subDays(1)->toDateString(),
                        'hours' => 2.5,
                        'description' => 'Demo work log',
                    ],
                    [
                        'billable' => true,
                    ]
                );

                TaskComment::firstOrCreate(
                    [
                        'task_id' => $task->id,
                        'user_id' => $finance?->id ?? 1,
                        'comment' => 'Demo comment for the task.',
                    ],
                    [
                        'is_internal' => true,
                    ]
                );
            }
        }
    }

    protected function seedServiceTypeItems($serviceTypes): void
    {
        $templates = [
            0 => [
                ['description' => 'جلسة تعريف', 'quantity' => 1, 'unit_price' => 500],
                ['description' => 'تصميم الشعار', 'quantity' => 1, 'unit_price' => 4000],
            ],
            1 => [
                ['description' => 'تصميم واجهة رئيسية', 'quantity' => 1, 'unit_price' => 3000],
                ['description' => 'صفحات داخلية', 'quantity' => 5, 'unit_price' => 750],
            ],
            2 => [
                ['description' => 'خطة محتوى شهرية', 'quantity' => 1, 'unit_price' => 2500],
                ['description' => 'تصاميم منشورات', 'quantity' => 12, 'unit_price' => 180],
            ],
        ];

        foreach ($templates as $index => $items) {
            $serviceType = $serviceTypes[$index] ?? null;
            if (!$serviceType) {
                continue;
            }

            foreach ($items as $order => $item) {
                ServiceTypeItem::updateOrCreate(
                    ['service_type_id' => $serviceType->id, 'description' => $item['description']],
                    [
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'sort_order' => $order,
                    ]
                );
            }
        }
    }
}
