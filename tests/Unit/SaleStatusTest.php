<?php

namespace Tests\Unit;

use App\Enums\SaleStatus;
use PHPUnit\Framework\TestCase;

class SaleStatusTest extends TestCase
{
    public function test_status_labels_match_business_terms(): void
    {
        $this->assertSame('Belum Dibayar', SaleStatus::Unpaid->label());
        $this->assertSame('Belum Dibayar Sepenuhnya', SaleStatus::PartiallyPaid->label());
        $this->assertSame('Sudah Dibayar', SaleStatus::Paid->label());
    }

    public function test_status_badge_classes_are_stable(): void
    {
        $this->assertSame('text-bg-secondary', SaleStatus::Unpaid->badgeClass());
        $this->assertSame('text-bg-warning', SaleStatus::PartiallyPaid->badgeClass());
        $this->assertSame('text-bg-success', SaleStatus::Paid->badgeClass());
    }
}
