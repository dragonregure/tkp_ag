<?php

namespace App\Repositories;

use App\Contracts\RoleRepositoryInterface;
use App\Support\Rbac\Permissions;
use App\Support\Rbac\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleRepository implements RoleRepositoryInterface
{
    private function dataTableQuery(): Builder
    {
        return Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name');
    }

    public function dataTableResponse(): JsonResponse
    {
        return DataTables::eloquent($this->dataTableQuery())
            ->addColumn('permissions_count', fn (Role $role): int => $role->permissions->count())
            ->addColumn('permissions_text', function (Role $role): string {
                return $role->permissions
                    ->pluck('name')
                    ->map(fn (string $permission): string => Permissions::label($permission))
                    ->implode(', ');
            })
            ->addColumn('actions', fn (Role $role): string => view('roles.partials.table-actions', compact('role'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $role = Role::query()->create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissions);

            return $role->load('permissions');
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data): Role {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $role->update([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissions);

            return $role->refresh()->load('permissions');
        });
    }

    public function delete(Role $role): void
    {
        if ($role->name === Roles::ADMIN) {
            throw new RuntimeException('Role admin tidak bisa dihapus.');
        }

        if ($role->users()->exists()) {
            throw new RuntimeException('Role masih dipakai oleh user dan tidak bisa dihapus.');
        }

        $role->delete();
    }
}
