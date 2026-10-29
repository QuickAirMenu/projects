<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-users')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = User::with('roles')->latest();
        if ($request->search) $query->where(fn($q) => $q->where('name','like',"%{$request->search}%")->orWhere('email','like',"%{$request->search}%"));
        if ($request->role)   $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        if ($request->has('status')) $query->where('is_active', $request->status);
        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();
        return view('users.index', compact('users','roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'job_title'  => 'nullable|string|max:100',
            'job_level'  => 'nullable|in:' . implode(',', array_keys(User::JOB_LEVELS)),
            'phone'      => 'nullable|string|max:20',
            'role'       => 'required|exists:roles,name',
            'language'   => 'nullable|in:ar,en',
            'send_invite'=> 'nullable|boolean',
        ]);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make(Str::random(32)),
            'job_title'  => $request->job_title,
            'job_level'  => $request->job_level,
            'phone'      => $request->phone,
            'language'   => $request->language ?? 'ar',
            'is_active'  => true,
        ]);
        $user->assignRole($request->role);

        if ($request->boolean('send_invite')) {
            $token = Str::random(64);
            $user->update([
                'invitation_token' => $token,
                'invitation_expires_at' => now()->addDays(7),
            ]);

            try {
                app(\App\Services\MailConfigService::class)->applyDynamicMailConfig();
                if (config('mail.mailers.smtp.host')) {
                    Mail::to($user->email)->send(
                        new UserInvitationMail($user, route('invitation.setup', $token))
                    );
                }
            } catch (\Throwable $e) {
                Log::error('User invitation email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('users.index')
            ->with('success', $request->boolean('send_invite')
                ? __('users.invite_sent')
                : __('users.user_added'));
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,'.$user->id,
            'job_title' => 'nullable|string|max:100',
            'job_level' => 'nullable|in:' . implode(',', array_keys(User::JOB_LEVELS)),
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|exists:roles,name',
            'language'  => 'nullable|in:ar,en',
        ]);

        $user->update($request->only(['name','email','job_title','job_level','phone','language']));
        if ($request->password) $user->update(['password' => Hash::make($request->password)]);
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', __('messages.cannot_delete_self'));
        $user->delete();
        return redirect()->route('users.index')->with('success', __('messages.deleted_successfully'));
    }

    public function invite(User $user)
    {
        if ($user->email_verified_at) {
            return back()->with('error', __('messages.user_already_active'));
        }

        $token = Str::random(64);
        $user->update([
            'invitation_token' => $token,
            'invitation_expires_at' => now()->addDays(7),
        ]);

        try {
            app(\App\Services\MailConfigService::class)->applyDynamicMailConfig();
            if (config('mail.mailers.smtp.host')) {
                Mail::to($user->email)->send(
                    new UserInvitationMail($user, route('invitation.setup', $token))
                );
            }
        } catch (\Throwable $e) {
            Log::error('User invitation resend failed: ' . $e->getMessage());
        }

        return back()->with('success', __('users.invite_resent'));
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return redirect()->route('users.index')
            ->with('success', $user->is_active ? __('users.user_activated') : __('users.user_deactivated'));
    }
}
