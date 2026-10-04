<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in(['customer', 'provider'])],
        ];
        foreach (ProviderProfileRequest::profileRules() as $field => $fieldRules) {
            $rules[$field] = $this->input('role') === 'provider' ? $fieldRules : ['exclude'];
        }
        return $rules;
    }
}
