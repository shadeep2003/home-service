<?php
namespace App\Http\Requests;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProviderProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === Role::Provider; }
    public static function profileRules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+() .-]{7,30}$/'],
            'service_area' => ['required', 'string', 'max:255'],
            'biography' => ['nullable', 'string', 'max:1500'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1', 'max:50'],
            'category_ids.*' => ['required', 'integer', 'distinct', Rule::exists('service_categories', 'id')->where('is_active', true)],
        ];
    }
    public function rules(): array { return \App\Services\AccountProfile::rules($this->user(), false) + self::profileRules() + ['is_available' => ['required', 'boolean'], 'is_working' => ['sometimes', 'boolean']]; }
}
