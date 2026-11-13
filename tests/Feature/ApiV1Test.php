<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'api@example.com', 'password' => 'password123',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_login_returns_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api@example.com',
            'password' => 'password123',
            'device_name' => 'test',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'roles']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'api@example.com',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_protected_endpoint_requires_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_me_and_lists_with_token(): void
    {
        $this->seedProjects();
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/me')->assertOk()->assertJsonPath('user.email', 'api@example.com');
        $this->getJson('/api/v1/projects')->assertOk()->assertJsonStructure(['data', 'meta']);
        $this->getJson('/api/v1/clients')->assertOk();
        $this->getJson('/api/v1/invoices')->assertOk();
        $this->getJson('/api/v1/quotes')->assertOk();
        $this->getJson('/api/v1/tasks')->assertOk();
        $this->getJson('/api/v1/dashboard')->assertOk()->assertJsonStructure(['projects_total', 'tasks_total', 'financial']);
    }

    public function test_logout_revokes_token(): void
    {
        Sanctum::actingAs($this->admin);
        $token = $this->admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();
    }

    private function seedProjects(): void
    {
        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $taskStatus = TaskStatus::create([
            'name_ar' => 'قيد العمل', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#8B5CF6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $client = Client::create([
            'name' => 'API Client', 'email' => 'api-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $project = Project::create([
            'name' => 'API Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 10,
            'created_by' => $this->admin->id,
        ]);
        Task::create([
            'project_id' => $project->id, 'title' => 'API Task', 'description' => 'x',
            'status_id' => $taskStatus->id, 'priority' => 'high', 'assigned_to' => $this->admin->id,
            'progress_percentage' => 0, 'created_by' => $this->admin->id,
        ]);
    }
}
