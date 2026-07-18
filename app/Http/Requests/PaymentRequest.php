<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Payment;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        if ($payment instanceof Payment) {
            return $this->user()?->can('update', $payment) ?? false;
        }

        return $this->user()?->can('create', Payment::class) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = [
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->isMethod('post')) {
            $rules['sale_id'] = ['required', 'exists:sales,id'];
        }

        return $rules;
    }
}
