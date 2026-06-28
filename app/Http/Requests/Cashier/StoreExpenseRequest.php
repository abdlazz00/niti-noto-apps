<?php

namespace App\Http\Requests\Cashier;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('cashier') ?? false;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:200'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'attachment'  => ['nullable', 'image', 'max:2048'],
        ];
    }
}
