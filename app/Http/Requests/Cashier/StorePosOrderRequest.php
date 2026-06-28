<?php

namespace App\Http\Requests\Cashier;

use Illuminate\Foundation\Http\FormRequest;

class StorePosOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('cashier') ?? false;
    }

    public function rules(): array
    {
        return [
            'table_id'     => ['required', 'integer', 'exists:tables,id'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.id'   => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty'  => ['required', 'integer', 'min:1'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }
}
