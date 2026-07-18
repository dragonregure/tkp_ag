<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Item;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        if ($item instanceof Item) {
            return $this->user()?->can('update', $item) ?? false;
        }

        return $this->user()?->can('create', Item::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('items', 'code')->ignore($itemId)],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
