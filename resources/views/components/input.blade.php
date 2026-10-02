@props(['name', 'label', 'type' => 'text'])
<div class="field">
<label for="{{ $name }}">{{ $label }}</label>
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
    @if($type !== 'password') value="{{ old($name) }}" @endif
    @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
    {{ $attributes }}>
@error($name)<p class="field-error" id="{{ $name }}-error">{{ $message }}</p>@enderror
</div>
