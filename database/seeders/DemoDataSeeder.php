<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo data is only available in local or testing environments.');
        }
        $this->call(ServiceCategorySeeder::class);
        $services = ['ac-repair', 'electrical', 'plumbing', 'cleaning', 'painting', 'gardening'];
        $areas = ['Matara', 'Galle', 'Colombo', 'Kandy', 'Weligama', 'Negombo'];
        $providers = []; $customers = []; $number = 3;
        for ($index = 0; $index < 8; $index++) {
            $name = 'Demo Isuru '.($index + 1);
            // Reuse our demo account on reruns; never change an existing real account.
            $user = User::where('name', $name)->where('email', 'like', 'isuru%@gmail.com')->first();
            $created = false;
            if (!$user) {
                do { $email = 'isuru'.$number++.'@gmail.com'; } while (User::where('email', $email)->exists());
                $user = new User(['name' => $name, 'email' => $email, 'password' => 'I12345678']);
                $user->role = $index < 6 ? Role::Provider : Role::Customer;
                $user->save(); $created = true;
            }
            if ($index < 6) {
                $providers[] = $user;
                if ($created) {
                    $user->providerProfile()->create([
                        'phone' => '+94 00 000 0000', // Deliberately non-dialable demo contact.
                        'service_area' => $areas[$index], 'experience_years' => [10, 5, 8, 3, 12, 4][$index],
                        'biography' => 'Sample profile for testing. Offers '.$services[$index].' services around '.$areas[$index].'. This is not a real service provider.',
                        'working_hours' => 'Monday–Saturday, 8:00 AM–6:00 PM',
                        'is_available' => $index !== 2, 'is_working' => $index === 1,
                    ]);
                    $slugs = [$services[$index]];
                    if (in_array($index, [1, 2])) { $slugs[] = 'ac-repair'; }
                    $user->serviceCategories()->attach(ServiceCategory::whereIn('slug', $slugs)->pluck('id'));
                }
            } else { $customers[] = $user; }
            if ($created && $index % 2 === 0) {
                $path = 'profile-photos/demo-'.$user->id.'.png';
                Storage::disk('local')->put($path, file_get_contents(database_path('seeders/fixtures/avatar-'.($index % 3).'.png')));
                $user->forceFill(['profile_photo_path' => $path])->save();
            }
            $this->command?->line($user->email.' | '.$user->role->value.' | '.$user->name);
        }
        foreach ($providers as $index => $provider) {
            // Leave the last provider unrated to demonstrate the empty state.
            if ($index === 5) { continue; }
            foreach ($customers as $offset => $customer) {
                $provider->reviews()->firstOrCreate(['customer_id' => $customer->id], [
                    'rating' => [5, 4, 3, 5, 4][($index + $offset) % 5],
                    'comment' => $offset === 0 ? 'Demo review: friendly communication and helpful service.' : 'Demo review: good service; appointment timing could be improved.',
                ]);
            }
        }
    }
}
