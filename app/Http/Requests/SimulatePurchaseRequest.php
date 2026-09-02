<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimulatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Enter a purchase amount.',
            'amount.numeric' => 'The purchase amount must be a number.',
            'amount.min' => 'The purchase amount must be at least 0.01.',
            'amount.max' => 'The purchase amount cannot exceed 100000.',
        ];
    }
}
