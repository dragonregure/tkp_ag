<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;

class Select2Request extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->route()?->getName()) {
            'items.select2' => $this->user()?->can('viewAny', Item::class) ?? false,
            'sales.select2' => $this->user()?->can('viewAny', Sale::class) ?? false,
            default => false,
        };
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'term' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
