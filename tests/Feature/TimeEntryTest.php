<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'time@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-time',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Time Client', 'email' => 'time-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Time Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 10000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $taskStatus = TaskStatus::create([
            'name_ar' => 'جديدة', 'name_en' => 'Todo', 'slug' => 'todo-time',
            'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $this->task = Task::create([
            'project_id' => $this->project->id, 'title' => 'Design homepage',
            'status_id' => $taskStatus->id, 'priority' => 'medium',
            'assigned_to' => $this->admin->id, 'actual_hours' => 0,
            'progress_percentage' => 0, 'created_by' => $this->admin->id,
        ]);
    }

    public function test_pages_render(): void
    {
        $this->actingAs($this->admin)->get(route('time-entries.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('time-entries.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('time-entries.bill.preview'))->assertOk();
    }

    public function test_entry_creation_updates_task_actual_hours(): void
    {
        $this->actingAs($this->admin)->post(route('time-entries.store'), [
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'user_id' => $this->admin->id,
            'work_date' => now()->toDateString(),
            'hours' => 3.5,
            'description' => 'Wireframes',
            'hourly_rate' => 120,
            'billable' => 1,
        ])->assertRedirect(route('time-entries.index'));

        $this->assertDatabaseHas('time_entries', ['description' => 'Wireframes', 'hours' => 3.5]);
        $this->assertEquals(3.5, (float) $this->task->fresh()->actual_hours);
    }

    public function test_billing_unbilled_entries_creates_invoice_and_marks_entries(): void
    {
        $a = $this->entry(['hours' => 2, 'hourly_rate' => 100]);
        $b = $this->entry(['hours' => 1.5, 'hourly_rate' => 200, 'description' => 'Landing page']);

        $this->actingAs($this->admin)
            ->post(route('time-entries.bill.store'), [
                'project_id' => $this->project->id,
                'from' => now()->subDays(2)->toDateString(),
                'to' => now()->toDateString(),
                'tax_percentage' => 15,
            ])
            ->assertRedirect();

        $invoice = Invoice::with('items')->firstOrFail();
        $this->assertSame($this->client->id, $invoice->client_id);
        $this->assertEquals(500, (float) $invoice->subtotal);
        $this->assertEquals(75, (float) $invoice->tax_amount);
        $this->assertEquals(575, (float) $invoice->total);
        $this->assertCount(2, $invoice->items);

        $this->assertSame($invoice->id, $a->fresh()->invoiced_invoice_id);
        $this->assertSame($invoice->id, $b->fresh()->invoiced_invoice_id);
    }

    public function test_cannot_bill_same_entries_twice(): void
    {
        $this->entry();

        $payload = [
            'project_id' => $this->project->id,
            'from' => now()->subDays(2)->toDateString(),
            'to' => now()->toDateString(),
            'tax_percentage' => 15,
        ];

        $this->actingAs($this->admin)->post(route('time-entries.bill.store'), $payload)->assertRedirect();
        $this->actingAs($this->admin)->post(route('time-entries.bill.store'), $payload)->assertSessionHasErrors('from');

        $this->assertSame(1, Invoice::count());
    }

    public function test_invoiced_entries_cannot_be_deleted(): void
    {
        $entry = $this->entry();
        $entry->update(['invoiced_invoice_id' => Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'additional', 'invoice_date' => now()->toDateString(),
            'subtotal' => 10, 'tax_percentage' => 15, 'tax_amount' => 1.5, 'discount_amount' => 0,
            'total' => 11.5, 'paid_amount' => 0, 'remaining_amount' => 11.5,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ])->id]);

        $this->actingAs($this->admin)
            ->delete(route('time-entries.destroy', $entry))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('time_entries', ['id' => $entry->id]);
    }

    protected function entry(array $overrides = []): TimeEntry
    {
        return TimeEntry::create(array_merge([
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'user_id' => $this->admin->id,
            'work_date' => now()->toDateString(),
            'hours' => 2,
            'description' => 'Homepage design',
            'billable' => true,
            'hourly_rate' => 100,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
