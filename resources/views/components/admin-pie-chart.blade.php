@props(['title', 'segments'])
@php
$total = array_sum(array_column($segments, 'value'));
$position = 0;
$stops = [];
foreach ($segments as $segment) {
    $end = $position + ($total ? $segment['value'] / $total * 100 : 0);
    $stops[] = $segment['color'].' '.$position.'% '.$end.'%';
    $position = $end;
}
@endphp
<section class="admin-chart"><h2>{{ $title }}</h2><div class="admin-chart-content"><div class="admin-pie" style="background: {{ $total ? 'conic-gradient('.implode(', ', $stops).')' : '#e6ece8' }}" role="img" aria-label="{{ $title }}: @foreach($segments as $segment){{ $segment['label'] }} {{ $segment['value'] }}{{ $loop->last ? '' : ', ' }}@endforeach"><div><strong>{{ $total }}</strong><span>Total</span></div></div><ul class="admin-chart-legend">@foreach($segments as $segment)<li><span class="chart-swatch" style="background:{{ $segment['color'] }}" aria-hidden="true"></span><span>{{ $segment['label'] }}</span><strong>{{ $segment['value'] }}</strong><small>{{ $total ? round($segment['value'] / $total * 100) : 0 }}%</small></li>@endforeach</ul></div>@if(!$total)<p class="muted">No data yet.</p>@endif</section>
