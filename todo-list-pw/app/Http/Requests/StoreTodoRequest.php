<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'task' => 'required|string|min:5|max:25',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'task.required' => 'Task Wajib Diisi',
            'task.min' => 'Task Minimal 5 Karakter',
            'task.max' => 'Task Maksimum 25 Karakter',
        ];
    }
}
