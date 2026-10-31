<?php

namespace App\Http\Controllers;

use App\Models\NumberSequence;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SetupController extends Controller
{
    public function generateToken(): string
    {
        $token = Str::random(48);

        Setting::updateOrCreate(
            ['key' => 'setup_token'],
            ['value' => $token, 'type' => 'string', 'group' => 'system']
        );

        Setting::updateOrCreate(
            ['key' => 'setup_token_expires_at'],
            ['value' => now()->addDays(7)->toDateTimeString(), 'type' => 'string', 'group' => 'system']
        );

        Setting::updateOrCreate(
            ['key' => 'setup_completed_at'],
            ['value' => null, 'type' => 'string', 'group' => 'system']
        );

        return $token;
    }

    public function getSetupUrl(): string
    {
        $token = $this->getSetupToken();
        if (! $token) {
            $token = $this->generateToken();
        }

        return url('/setup/'.$token);
    }

    public function show(Request $request, string $token)
    {
        if (User::count() > 0) {
            return redirect()->route('login')
                ->with('info', 'النظام تم إعداده بالفعل.');
        }

        if (! $this->isSetupTokenValid($token)) {
            abort(403, 'رابط الإعداد غير صالح أو منتهي الصلاحية.');
        }

        return view('setup.welcome', ['secret' => $token]);
    }

    public function store(Request $request, string $token)
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        if (! $this->isSetupTokenValid($token)) {
            abort(403, 'رابط الإعداد غير صالح أو منتهي الصلاحية.');
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'currency' => 'required|string|in:SAR,USD,EUR,AED,EGP',
            'language' => 'required|string|in:ar,en',
            'timezone' => 'required|string',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $this->seedRolesAndPermissions();

        $admin = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'is_active' => true,
            'language' => $validated['language'],
        ]);
        $admin->assignRole('super-admin');

        $settings = [
            'company_name' => $validated['company_name'],
            'currency' => $validated['currency'],
            'language' => $validated['language'],
            'timezone' => $validated['timezone'],
            'tax_rate' => $validated['tax_rate'],
        ];
        foreach ($settings as $key => $value) {
            Setting::create([
                'key' => $key,
                'value' => $value,
                'type' => 'string',
                'group' => 'company',
            ]);
        }

        $this->seedProjectStatuses();
        $this->seedTaskStatuses();
        $this->seedServiceTypes();
        $this->seedNumberSequences();

        // Invalidate token
        Setting::updateOrCreate(
            ['key' => 'setup_completed_at'],
            ['value' => now()->toDateTimeString(), 'type' => 'string', 'group' => 'system']
        );

        return redirect()->route('login')
            ->with('success', 'تم إعداد النظام بنجاح! يمكنك الآن تسجيل الدخول.');
    }

    public function pending()
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        return view('setup.pending');
    }

    private function getSetupToken(): string
    {
        $token = Setting::where('key', 'setup_token')->first();

        return $token?->value ?? '';
    }

    private function isSetupTokenValid(?string $token): bool
    {
        if (! $token) {
            return false;
        }

        $stored = Setting::where('key', 'setup_token')->first();
        if (! $stored || $stored->value !== $token) {
            return false;
        }

        $expiresAt = Setting::where('key', 'setup_token_expires_at')->first();
        if ($expiresAt && now()->gt($expiresAt->value)) {
            return false;
        }

        $usedAt = Setting::where('key', 'setup_completed_at')->first();
        if ($usedAt) {
            return false;
        }

        return true;
    }

    private function seedRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view-dashboard', 'manage-users', 'manage-roles', 'manage-settings',
            'view-clients', 'create-clients', 'edit-clients', 'delete-clients',
            'view-projects', 'create-projects', 'edit-projects', 'delete-projects',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks',
            'view-quotes', 'create-quotes', 'edit-quotes', 'delete-quotes', 'send-quotes',
            'view-invoices', 'create-invoices', 'edit-invoices', 'delete-invoices',
            'view-payments', 'create-payments', 'edit-payments', 'delete-payments',
            'view-contracts', 'create-contracts', 'edit-contracts', 'delete-contracts',
            'view-reports', 'export-data', 'import-data',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($permissions);

        Role::firstOrCreate(['name' => 'project-manager']);
        Role::firstOrCreate(['name' => 'team-member']);
        Role::firstOrCreate(['name' => 'finance']);
        Role::firstOrCreate(['name' => 'client']);
    }

    private function seedProjectStatuses(): void
    {
        $statuses = [
            ['name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'new', 'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true],
            ['name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress', 'color' => '#F59E0B', 'sort_order' => 2, 'is_default' => false, 'is_active' => true],
            ['name_ar' => 'مكتمل', 'name_en' => 'Completed', 'slug' => 'completed', 'color' => '#10B981', 'sort_order' => 3, 'is_default' => false, 'is_active' => true],
            ['name_ar' => 'مؤرشف', 'name_en' => 'Archived', 'slug' => 'archived', 'color' => '#6B7280', 'sort_order' => 4, 'is_default' => false, 'is_active' => true],
        ];
        foreach ($statuses as $status) {
            ProjectStatus::create($status);
        }
    }

    private function seedTaskStatuses(): void
    {
        $statuses = [
            ['name_ar' => 'جديد', 'name_en' => 'To Do', 'slug' => 'todo', 'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_active' => true],
            ['name_ar' => 'قيد العمل', 'name_en' => 'In Progress', 'slug' => 'in-progress', 'color' => '#3B82F6', 'sort_order' => 2, 'is_default' => false, 'is_active' => true],
            ['name_ar' => 'مراجعة', 'name_en' => 'Review', 'slug' => 'review', 'color' => '#F59E0B', 'sort_order' => 3, 'is_default' => false, 'is_active' => true],
            ['name_ar' => 'مكتمل', 'name_en' => 'Done', 'slug' => 'done', 'color' => '#10B981', 'sort_order' => 4, 'is_default' => false, 'is_active' => true],
        ];
        foreach ($statuses as $status) {
            TaskStatus::create($status);
        }
    }

    private function seedServiceTypes(): void
    {
        $types = [
            ['name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'خدمات التصميم', 'description_en' => 'Design services', 'is_active' => true, 'sort_order' => 1],
            ['name_ar' => 'تطوير', 'name_en' => 'Development', 'description_ar' => 'خدمات التطوير', 'description_en' => 'Development services', 'is_active' => true, 'sort_order' => 2],
            ['name_ar' => 'استشارات', 'name_en' => 'Consulting', 'description_ar' => 'خدمات استشارية', 'description_en' => 'Consulting services', 'is_active' => true, 'sort_order' => 3],
        ];
        foreach ($types as $type) {
            ServiceType::create($type);
        }
    }

    private function seedNumberSequences(): void
    {
        $yearMonth = now()->format('Ym');
        $sequences = [
            ['prefix' => 'QT', 'year_month' => $yearMonth, 'last_number' => 0],
            ['prefix' => 'INV', 'year_month' => $yearMonth, 'last_number' => 0],
            ['prefix' => 'CON', 'year_month' => $yearMonth, 'last_number' => 0],
            ['prefix' => 'PRJ', 'year_month' => $yearMonth, 'last_number' => 0],
        ];
        foreach ($sequences as $seq) {
            NumberSequence::create($seq);
        }
    }
}
