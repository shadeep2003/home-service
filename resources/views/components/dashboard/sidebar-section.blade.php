@props(['group'])
<div class="dashboard-nav-group">
    <p class="dashboard-nav-group__title">{{ $group['label'] }}</p>
    <ul>@foreach($group['items'] as $item)<li><x-dashboard.sidebar-item :item="$item" /></li>@endforeach</ul>
</div>
