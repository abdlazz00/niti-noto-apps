<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('expenseCategory');
        $id = $category ? $category->id : null;

        return [
            'name' => ['required', 'string', 'max:100', 'unique:expense_categories,name,' . $id],
        ];
    }
}
