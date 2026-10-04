<?php
namespace Database\Seeders;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Electrical' => 'From lighting to everyday electrical fixes.',
            'Plumbing' => 'A helping hand with leaks, taps and pipes.',
            'Cleaning' => 'Fresh spaces, from kitchen to living room.',
            'Painting' => 'A fresh coat. A whole new feeling.',
            'AC Repair' => 'Keep your home cool and comfortable.',
            'Gardening' => 'A little care for your outdoor sanctuary.',
        ] as $name => $description) {
            // Re-running preserves administrator edits and activation choices.
            ServiceCategory::firstOrCreate(['slug' => Str::slug($name)], compact('name', 'description') + ['icon' => $name, 'is_active' => true]);
        }
    }
}
