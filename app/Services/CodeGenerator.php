<?php

namespace App\Services;

use App\Contracts\CodeGeneratorInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CodeGenerator implements CodeGeneratorInterface
{
    public function generate(string $table, string $prefix, CarbonInterface|string|null $date = null): string
    {
        $datePart = CarbonImmutable::parse($date ?? now())->format('Ymd');
        $codePrefix = sprintf('%s-%s-', $prefix, $datePart);
        $latestCode = DB::table($table)
            ->where('code', 'like', $codePrefix . '%')
            ->lockForUpdate()
            ->orderByDesc('code')
            ->value('code');

        $nextSequence = $latestCode === null
            ? 1
            : ((int) substr((string) $latestCode, -4)) + 1;

        return sprintf('%s%04d', $codePrefix, $nextSequence);
    }
}
