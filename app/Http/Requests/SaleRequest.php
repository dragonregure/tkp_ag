<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Sale;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sale = $this->route('sale');

        if ($sale instanceof Sale) {
            return $this->user()?->can('update', $sale) ?? false;
        }

        return $this->user()?->can('create', Sale::class) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'sale_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
