<?php

namespace App\Http\Requests;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;

class DataTableDateFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->route()?->getName()) {
            'sales.data' => $this->user()?->can('viewAny', Sale::class) ?? false,
            'payments.data' => $this->user()?->can('viewAny', Payment::class) ?? false,
            default => false,
        };
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
