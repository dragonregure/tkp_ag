<?php

namespace App\Contracts;

use Illuminate\Http\JsonResponse;

interface PermissionRepositoryInterface
{
    public function dataTableResponse(): JsonResponse;
}
