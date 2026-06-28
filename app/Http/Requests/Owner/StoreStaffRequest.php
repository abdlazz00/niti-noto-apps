<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('owner');
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'role'      => 'required|in:owner,cashier,staff',
            'photo'     => 'nullable|image|max:2048',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'join_date' => 'nullable|date',
            'notes'     => 'nullable|string',
        ];
    }
}
