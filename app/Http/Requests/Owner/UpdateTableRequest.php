<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['owner', 'cashier']);
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'number'    => ['required', 'integer', 'min:1', 'unique:tables,number,' . $this->route('table')->id],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
