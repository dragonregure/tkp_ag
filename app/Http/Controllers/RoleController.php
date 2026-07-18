<?php

namespace App\Http\Controllers;

use App\Contracts\RoleRepositoryInterface;
use App\Http\Requests\RoleRequest;
use App\Support\Rbac\Permissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleRepositoryInterface $roles)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        return view('roles.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return $this->roles->dataTableResponse();
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.form', [
            'role' => new Role(),
            'groupedPermissions' => Permissions::grouped(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $this->roles->create($request->validated());

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('roles.form', [
            'role' => $role->load('permissions'),
            'groupedPermissions' => Permissions::grouped(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $this->roles->update($role, $request->validated());

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        try {
            $this->roles->delete($role);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }
}
