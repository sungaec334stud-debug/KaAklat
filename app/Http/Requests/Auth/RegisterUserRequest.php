<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email'),
            'student_id' => is_string($this->input('student_id')) ? trim($this->input('student_id')) : $this->input('student_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[^@\s]+@udd\.edu\.ph$/D',
                Rule::unique('users', 'email'),
            ],
            // Student IDs remain strings; the university format is not yet confirmed.
            'student_id' => ['required', 'string', 'max:255', Rule::unique('users', 'student_id')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered. Sign in or request another verification link below.',
            'student_id.unique' => 'This student ID is already registered. Use your own unique university-issued student ID.',
        ];
    }
}
