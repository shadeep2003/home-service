{{-- Static presentation data only; replace with catalogue data in a later phase. --}}
<div class="service-grid">
@foreach (['Electrical' => 'From lighting to everyday electrical fixes.', 'Plumbing' => 'A helping hand with leaks, taps and pipes.', 'Cleaning' => 'Fresh spaces, from kitchen to living room.', 'Painting' => 'A fresh coat. A whole new feeling.', 'AC Repair' => 'Keep your home cool and comfortable.', 'Gardening' => 'A little care for your outdoor sanctuary.'] as $name => $description)
<x-service-card :name="$name" :description="$description" />
@endforeach
</div>
