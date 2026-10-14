<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('outlet');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($outletId = $request->input('outlet_id')) {
            $query->where('outlet_id', $outletId);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $outlets = Outlet::orderBy('name')->get();

        return view('users.index', compact('users', 'outlets'));
    }

    public function create(): View
    {
        $outlets = Outlet::orderBy('name')->get();

        return view('users.create', compact('outlets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:super_admin,franchise_owner,outlet_manager,staff'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        return redirect()->route('users.index')
            ->with('status', "User '{$user->name}' created successfully.");
    }

    public function edit(User $user): View
    {
        $outlets = Outlet::orderBy('name')->get();

        return view('users.edit', compact('user', 'outlets'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:super_admin,franchise_owner,outlet_manager,staff'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('status', "User '{$user->name}' updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own active account.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('status', "User '{$name}' removed.");
    }
}
