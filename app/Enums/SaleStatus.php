<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Dibayar',
            self::PartiallyPaid => 'Belum Dibayar Sepenuhnya',
            self::Paid => 'Sudah Dibayar',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Unpaid => 'text-bg-secondary',
            self::PartiallyPaid => 'text-bg-warning',
            self::Paid => 'text-bg-success',
        };
    }
}
