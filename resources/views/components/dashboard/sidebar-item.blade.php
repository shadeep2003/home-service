@props(['item'])
<a class="dashboard-nav-item" href="{{ $item['url'] }}" aria-label="{{ $item['label'] }}" title="{{ $item['label'] }}" @if($item['active']) aria-current="page" @endif @if($item['section']) data-dashboard-section="{{ $item['section'] }}" data-route-active="{{ $item['active'] ? 'true' : 'false' }}" @endif>
    <x-icon :name="$item['icon']" class="dashboard-nav-item__icon" />
    <span class="dashboard-sidebar-label">{{ $item['label'] }}</span>
    <span class="dashboard-nav-item__indicator" aria-hidden="true"></span>
</a>
