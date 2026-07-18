<?php

namespace App\Repositories;

use App\Contracts\PermissionRepositoryInterface;
use App\Support\Rbac\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class PermissionRepository implements PermissionRepositoryInterface
{
    private function dataTableQuery(): Builder
    {
        return Permission::query()->orderBy('name');
    }

    public function dataTableResponse(): JsonResponse
    {
        return DataTables::eloquent($this->dataTableQuery())
            ->addColumn('module', fn (Permission $permission): string => Permissions::group($permission->name))
            ->addColumn('label', fn (Permission $permission): string => Permissions::label($permission->name))
            ->toJson();
    }
}
