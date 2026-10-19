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
        $currentUser = $request->user();
        $query = User::with('outlet');

        // Scope users by role
        if (! $currentUser->isSuperAdmin()) {
            if ($currentUser->isFranchiseOwner()) {
                $accessibleIds = $currentUser->accessibleOutletsQuery()->pluck('id');
                $query->whereIn('outlet_id', $accessibleIds);
            } else {
                $query->where('outlet_id', $currentUser->outlet_id ?? 0);
            }
        }

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
            if ($currentUser->canManageOutlet((int) $outletId)) {
                $query->where('outlet_id', $outletId);
            }
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $outlets = $currentUser->accessibleOutletsQuery()->orderBy('name')->get();

        return view('users.index', compact('users', 'outlets'));
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();
        $outlets = $currentUser->accessibleOutletsQuery()->orderBy('name')->get();

        return view('users.create', compact('outlets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        // Prevent privilege escalation: restrict assignable roles by operator level
        $allowedRoles = match ($currentUser->role) {
            'super_admin' => ['super_admin', 'franchise_owner', 'outlet_manager', 'staff'],
            'franchise_owner' => ['outlet_manager', 'staff'],
            default => ['staff'],
        };

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:'.implode(',', $allowedRoles)],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['outlet_id']) && ! $currentUser->canManageOutlet((int) $validated['outlet_id'])) {
            abort(403, 'Unauthorized to assign users to this outlet.');
        }

        if (! $currentUser->isSuperAdmin() && empty($validated['outlet_id'])) {
            $validated['outlet_id'] = $currentUser->outlet_id;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        return redirect()->route('users.index')
            ->with('status', "User '{$user->name}' created successfully.");
    }

    public function edit(Request $request, User $user): View
    {
        $currentUser = $request->user();
        $this->authorizeUserManagement($currentUser, $user);

        $outlets = $currentUser->accessibleOutletsQuery()->orderBy('name')->get();

        return view('users.edit', compact('user', 'outlets'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();
        $this->authorizeUserManagement($currentUser, $user);

        $allowedRoles = match ($currentUser->role) {
            'super_admin' => ['super_admin', 'franchise_owner', 'outlet_manager', 'staff'],
            'franchise_owner' => ['outlet_manager', 'staff'],
            default => ['staff'],
        };

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:'.implode(',', $allowedRoles)],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['outlet_id']) && ! $currentUser->canManageOutlet((int) $validated['outlet_id'])) {
            abort(403, 'Unauthorized to assign user to this outlet.');
        }

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

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();
        $this->authorizeUserManagement($currentUser, $user);

        if ($currentUser->id === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own active account.']);
        }

        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the sole remaining Corporate Super Admin account.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('status', "User '{$name}' removed.");
    }

    protected function authorizeUserManagement(User $currentUser, User $targetUser): void
    {
        if ($currentUser->isSuperAdmin()) {
            return;
        }

        if ($targetUser->isSuperAdmin()) {
            abort(403, 'Unauthorized to modify corporate administrators.');
        }

        if ($currentUser->isFranchiseOwner()) {
            if ($targetUser->isFranchiseOwner() && $currentUser->id !== $targetUser->id) {
                abort(403, 'Unauthorized to modify other franchise owners.');
            }
            if ($targetUser->outlet_id && ! $currentUser->canManageOutlet((int) $targetUser->outlet_id)) {
                abort(403, 'Unauthorized to manage staff outside your franchise.');
            }

            return;
        }

        // Outlet Manager can only manage staff in their assigned store
        if ($currentUser->isOutletManager()) {
            if ($targetUser->role !== 'staff' && $currentUser->id !== $targetUser->id) {
                abort(403, 'Outlet managers can only manage store staff.');
            }
            if ($targetUser->outlet_id !== $currentUser->outlet_id) {
                abort(403, 'Unauthorized to manage staff outside your store.');
            }

            return;
        }

        abort(403, 'Unauthorized to perform user administration.');
    }
}
