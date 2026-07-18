<?php

namespace App\Contracts;

use Carbon\CarbonInterface;

interface DashboardRepositoryInterface
{
    /**
     * @return array{transaction_count:int,sales_amount:float,item_qty:int}
     */
    public function summary(CarbonInterface $startDate, CarbonInterface $endDate): array;

    /**
     * @return array<int, array{month:string,total:float}>
     */
    public function salesAmountPerMonth(CarbonInterface $startDate, CarbonInterface $endDate): array;

    /**
     * @return array<int, array{name:string,qty:int}>
     */
    public function itemQty(CarbonInterface $startDate, CarbonInterface $endDate): array;
}
