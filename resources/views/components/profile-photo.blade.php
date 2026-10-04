@props(['user', 'class' => ''])
<img class="user-profile-photo {{ $class }}" src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}'s profile picture" width="80" height="80" loading="lazy">
