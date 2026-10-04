@props(['user'])
<section class="photo-upload-panel" aria-label="Profile picture">
<x-profile-photo :user="$user" />
<div class="photo-upload-content"><h2>Your profile picture</h2><p>Choose a photo so people can recognise you. JPG, PNG or WebP · up to 2 MB.</p>
@if(session('photo_status'))<p role="status" class="photo-success">{{ session('photo_status') }}</p>@endif
<form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">@csrf
<label for="profile-photo">Choose a profile photo</label><input id="profile-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
@error('photo')<p class="field-error">{{ $message }}</p>@enderror
<button class="button" type="submit">Upload photo</button></form></div>
</section>
