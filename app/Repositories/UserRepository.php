<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserRepository implements UserRepositoryInterface
{
    private function dataTableQuery(): Builder
    {
        return User::query()->with('roles')->latest();
    }

    public function dataTableResponse(): JsonResponse
    {
        return DataTables::eloquent($this->dataTableQuery())
            ->addColumn('roles_text', fn (User $user): string => $user->roles->pluck('name')->implode(', '))
            ->addColumn('actions', fn (User $user): string => view('users.partials.table-actions', compact('user'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(array $data): User
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        $data['password'] = Hash::make($data['password']);

        $user = User::query()->create($data);
        $user->syncRoles($roles);

        return $user->load('roles');
    }

    public function update(User $user, array $data): User
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        if (($data['password'] ?? null) === null) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        $user->syncRoles($roles);

        return $user->refresh()->load('roles');
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
