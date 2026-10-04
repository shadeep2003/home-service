<?php
namespace App\Http\Requests;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === Role::Admin; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('service_categories')->ignore($this->route('category'))],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('service_categories')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:1500'],
            'icon' => ['nullable', Rule::in(['Electrical', 'Plumbing', 'Cleaning', 'Painting', 'AC Repair', 'Gardening', 'check'])],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
