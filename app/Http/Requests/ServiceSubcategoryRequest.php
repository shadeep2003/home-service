<?php
namespace App\Http\Requests;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ServiceSubcategoryRequest extends FormRequest
{
    public function authorize(): bool {
        return $this->user()?->role === Role::Provider && !$this->user()->suspended_at
            && (!$this->route('subcategory') || $this->route('subcategory')->owner_id === $this->user()->id);
    }
    protected function prepareForValidation(): void {
        $name = $this->input('name');
        if (is_string($name)) { $this->merge(['name' => trim($name), 'name_key' => mb_strtolower(trim($name))]); }
    }
    public function rules(): array {
        return [
            'service_category_id' => ['required', 'integer', Rule::exists('service_categories', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:100'],
            'name_key' => ['required', 'string', 'max:100', Rule::unique('service_subcategories')->where('service_category_id', $this->input('service_category_id'))->ignore($this->route('subcategory')?->id)],
            'description' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
