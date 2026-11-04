<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\DeletesRestricted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de usuarios: administradores y docentes (docente = user con rol teacher).
 */
class UserController extends Controller
{
    use DeletesRestricted;

    public function index(Request $request): View
    {
        $request->validate(['role' => ['nullable', Rule::enum(UserRole::class)]]);

        $users = User::query()
            ->search($request->query('q'), ['name', 'email', 'institutional_id'])
            ->when($request->query('role'), fn ($q, $role) => $q->where('role', $role))
            ->withCount('courseOfferings')
            ->orderBy('role')->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        $role = UserRole::tryFrom((string) $request->query('role')) ?? UserRole::Teacher;

        return view('admin.users.form', ['user' => new User(['role' => $role, 'is_active' => true])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->userData());

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->userData());

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['general' => 'No puede eliminar su propia cuenta.']);
        }

        return $this->deleteRestricted($user, 'admin.users.index', 'el usuario');
    }
}
