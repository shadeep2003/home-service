@props(['categories', 'profile' => null, 'selected' => []])
<fieldset class="professional-fields"><legend>Professional profile</legend><div class="professional-fields-grid">
@foreach (['phone' => 'Phone number', 'service_area' => 'Service area / location', 'biography' => 'Short bio', 'experience_years' => 'Years of experience', 'working_hours' => 'Working hours (optional)'] as $field => $label)
<div class="field professional-field-{{ $field }}"><label for="{{ $field }}">{{ $label }}</label>
@if ($field === 'biography')
<textarea id="{{ $field }}" name="{{ $field }}" maxlength="1500" rows="4">{{ old($field, $profile?->$field) }}</textarea>
@else
<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $profile?->$field) }}" type="{{ $field === 'experience_years' ? 'number' : 'text' }}" @if($field === 'experience_years') min="0" max="80" @else maxlength="{{ $field === 'phone' ? 30 : 255 }}" @endif>
@endif
@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>
@endforeach
</div><div class="field professional-categories"><span id="categories-label">Service categories (select at least one)</span>
<div class="professional-category-options" aria-labelledby="categories-label">
@forelse ($categories as $category)
<label class="checkbox-label"><input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, (array) old('category_ids', $selected)))> {{ $category->name }}</label>
@empty
<p>No active categories are available. Provider registration will be available once categories are added.</p>
@endforelse
</div>
@error('category_ids')<p class="field-error">{{ $message }}</p>@enderror
@foreach ($errors->get('category_ids.*') as $messages)
@foreach ($messages as $message)<p class="field-error">{{ $message }}</p>@endforeach
@endforeach
</div></fieldset>
