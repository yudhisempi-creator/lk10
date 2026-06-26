<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Verifikasi bahwa todo milik user yang login
     */
    public function authorize(): bool
    {
        $todo = $this->route('todo');
        return $todo->user_id === auth()->id();
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
            'is_done' => 'required|boolean',
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
            'is_done.required' => 'Status Selesai Wajib Diisi',
            'is_done.boolean' => 'Format Status Tidak Valid',
        ];
    }
}
