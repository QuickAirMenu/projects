<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
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
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'job_title' => 'nullable|string|max:100',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|exists:roles,name',
            'language'  => 'nullable|in:ar,en',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'job_title' => $request->job_title,
            'phone'     => $request->phone,
            'language'  => $request->language ?? 'ar',
            'is_active' => true,
        ]);
        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', __('messages.created_successfully'));
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
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|exists:roles,name',
        ]);

        $user->update($request->only(['name','email','job_title','phone','language']));
        if ($request->password) $user->update(['password' => Hash::make($request->password)]);
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error','لا يمكن حذف حسابك الخاص');
        $user->delete();
        return redirect()->route('users.index')->with('success', __('messages.deleted_successfully'));
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return redirect()->route('users.index')
            ->with('success', $user->is_active ? 'تم تفعيل المستخدم' : 'تم تعطيل المستخدم');
    }
}
