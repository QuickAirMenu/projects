<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TeamPayoutBatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $dev1;

    protected User $dev2;

    protected Account $egp;

    protected Project $project;

    protected TaskStatus $taskStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'batch@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
        $this->dev1 = User::create(['name' => 'Dev One', 'email' => 'dev1@example.com', 'password' => 'password', 'is_active' => true, 'language' => 'en']);
        $this->dev2 = User::create(['name' => 'Dev Two', 'email' => 'dev2@example.com', 'password' => 'password', 'is_active' => true, 'language' => 'en']);

        $this->egp = Account::create([
            'name' => 'Egypt Bank', 'type' => 'bank', 'currency' => 'EGP',
            'opening_balance' => 100000, 'exchange_rate_to_base' => 0.075, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-tpb',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $client = Client::create([
            'name' => 'Batch Client', 'email' => 'batch-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Batch Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $this->taskStatus = TaskStatus::create([
            'name_ar' => 'جديدة', 'name_en' => 'Todo', 'slug' => 'todo-tpb',
            'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
    }

    public function test_batch_pay_creates_one_transaction_per_member_with_fx(): void
    {
        $t1 = $this->task($this->dev1, ['cost_amount' => 3000]);
        $t2 = $this->task($this->dev1, ['cost_amount' => 1000]);
        $t3 = $this->task($this->dev2, ['cost_amount' => 1000, 'cost_currency' => 'SAR', 'cost_exchange_rate' => 1]);

        $this->actingAs($this->admin)->post(route('reports.team-payouts.pay'), [
            'task_ids' => [$t1->id, $t2->id, $t3->id],
            'account_id' => $this->egp->id,
            'payout_date' => now()->toDateString(),
            'note' => 'Monthly',
        ])->assertRedirect();

        $this->assertSame(2, AccountTransaction::where('category', 'team_payout')->count());

        $dev1Tx = AccountTransaction::where('description', 'like', '%Dev One%')->firstOrFail();
        $this->assertEquals(4000, (float) $dev1Tx->amount);
        $this->assertEquals(300, (float) $dev1Tx->base_amount);

        $dev2Tx = AccountTransaction::where('description', 'like', '%Dev Two%')->firstOrFail();
        $this->assertEquals(13333.33, (float) $dev2Tx->amount);
        $this->assertEquals(1000, (float) $dev2Tx->base_amount);

        foreach ([$t1, $t2, $t3] as $task) {
            $this->assertNotNull($task->fresh()->cost_paid_at);
            $this->assertSame($this->egp->id, $task->fresh()->paid_account_id);
            $this->assertNotNull($task->fresh()->payout_group);
        }

        $this->assertSame((float) 100000 - 4000 - 13333.33, $this->egp->fresh()->balance());
    }

    public function test_already_paid_task_is_rejected(): void
    {
        $task = $this->task($this->dev1, ['cost_paid_at' => now()]);

        $this->actingAs($this->admin)->post(route('reports.team-payouts.pay'), [
            'task_ids' => [$task->id],
            'account_id' => $this->egp->id,
            'payout_date' => now()->toDateString(),
        ])->assertSessionHasErrors('tasks');
    }

    public function test_member_statement_pdf_renders(): void
    {
        $task = $this->task($this->dev1, ['cost_paid_at' => now(), 'paid_account_id' => $this->egp->id]);

        $response = $this->actingAs($this->admin)->get(route('reports.team-payouts.statement', [
            'user' => $this->dev1->id,
            'from' => now()->subMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_payout_requires_finance_permission(): void
    {
        $task = $this->task($this->dev1);
        $member = User::create(['name' => 'Member', 'email' => 'm@example.com', 'password' => 'password', 'is_active' => true, 'language' => 'en']);
        $member->assignRole('team-member');

        $this->actingAs($member)->post(route('reports.team-payouts.pay'), [
            'task_ids' => [$task->id],
            'account_id' => $this->egp->id,
            'payout_date' => now()->toDateString(),
        ])->assertForbidden();
    }

    protected function task(User $user, array $overrides = []): Task
    {
        return Task::create(array_merge([
            'project_id' => $this->project->id, 'title' => 'Task for '.$user->name,
            'status_id' => $this->taskStatus->id, 'priority' => 'medium',
            'assigned_to' => $user->id, 'actual_hours' => 0, 'progress_percentage' => 0,
            'cost_amount' => 3000, 'cost_currency' => 'EGP', 'cost_exchange_rate' => 0.075,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
