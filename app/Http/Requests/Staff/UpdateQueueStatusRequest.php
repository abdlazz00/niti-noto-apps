<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQueueStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('staff') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:sedang_dibuat,siap_diambil'],
        ];
    }
}
