<?php

namespace App\Contracts;

use Carbon\CarbonInterface;

interface CodeGeneratorInterface
{
    public function generate(string $table, string $prefix, CarbonInterface|string|null $date = null): string;
}
