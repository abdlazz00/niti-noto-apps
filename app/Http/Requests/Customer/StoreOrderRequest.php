<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'       => ['required', 'array', 'min:1'],
            'items.*.id'  => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ];
    }
}
