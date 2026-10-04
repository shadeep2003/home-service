<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;
    public function test_demo_data_preserves_existing_accounts_and_is_repeatable(): void
    {
        Storage::fake('local');
        $real = User::factory()->create(['email' => 'isuru3@gmail.com', 'name' => 'Existing account']);
        $password = $real->password;
        $this->seed(DemoDataSeeder::class);
        $this->assertDatabaseCount('users', 9);
        $this->assertDatabaseCount('provider_profiles', 6);
        $this->assertDatabaseCount('provider_reviews', 10);
        $this->assertTrue(Hash::check('I12345678', User::where('email', 'isuru4@gmail.com')->firstOrFail()->password));
        $this->assertSame($password, $real->fresh()->password);
        $this->seed(DemoDataSeeder::class);
        $this->assertDatabaseCount('users', 9);
        $this->assertDatabaseCount('provider_reviews', 10);
    }
}
