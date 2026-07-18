<?php

namespace App\Http\Controllers;

use App\Contracts\DashboardRepositoryInterface;
use App\Http\Requests\DashboardFilterRequest;
use App\Support\Rbac\Permissions;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardRepositoryInterface $dashboard)
    {
    }

    public function index(DashboardFilterRequest $request): View
    {
        Gate::authorize(Permissions::DASHBOARD_VIEW);

        $data = $request->validated();
        $startDate = CarbonImmutable::parse($data['start_date'] ?? now()->startOfMonth()->toDateString());
        $endDate = CarbonImmutable::parse($data['end_date'] ?? now()->endOfMonth()->toDateString());

        return view('dashboard.index', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'summary' => $this->dashboard->summary($startDate, $endDate),
            'salesChart' => $this->dashboard->salesAmountPerMonth($startDate, $endDate),
            'itemChart' => $this->dashboard->itemQty($startDate, $endDate),
        ]);
    }
}
