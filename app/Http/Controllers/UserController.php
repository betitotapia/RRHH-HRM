<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = [
        'super_admin' => 'Superusuario',
        'admin' => 'Administrador',
        'hr' => 'Recursos humanos',
        'manager' => 'Jefe / Manager',
        'supervisor' => 'Supervisor',
        'analyst' => 'Analista',
        'assistant' => 'Auxiliar',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $users = User::query()
            ->where('role', '!=', 'employee')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', ['users' => $users, 'roles' => self::ROLES, 'search' => $search]);
    }

    public function create(Request $request)
    {
        return view('users.create', [
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Usuario creado y acceso configurado correctamente.');
    }

    public function edit(User $user)
    {
        $this->ensureSystemUser($user);
        return view('users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSystemUser($user);
        $validated = $this->validateUser($request, $user);
        $validated['is_active'] = $request->boolean('is_active');
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);
        if (! $user->is_active) {
            $user->tokens()->delete();
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleStatus(User $user)
    {
        $this->ensureSystemUser($user);
        if (auth()->id() === $user->id && $user->is_active) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }
        $user->update(['is_active' => ! $user->is_active]);
        if (! $user->is_active) {
            $user->tokens()->delete();
        }

        return back()->with('success', $user->is_active ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function sendResetLink(User $user)
    {
        $this->ensureSystemUser($user);
        $status = Password::sendResetLink(['email' => $user->email]);
        return back()->with($status === Password::RESET_LINK_SENT ? 'success' : 'error', __($status));
    }

    public function destroy(User $user)
    {
        $this->ensureSystemUser($user);
        if (auth()->id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function ensureSystemUser(User $user): void
    {
        abort_if($user->role === 'employee', 404);
    }

}
