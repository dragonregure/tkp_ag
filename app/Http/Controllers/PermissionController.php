<?php

namespace App\Http\Controllers;

use App\Contracts\PermissionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionRepositoryInterface $permissions)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Permission::class);

        return view('permissions.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        return $this->permissions->dataTableResponse();
    }
}
