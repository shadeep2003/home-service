<?php
namespace App\Models;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    use HasFactory;
    public function providerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }
    public function serviceCategories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class, 'provider_services', 'provider_id', 'service_category_id')->withTimestamps();
    }
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProviderReview::class, 'provider_id');
    }
    public function profilePhotoUrl(): string
    {
        return $this->profile_photo_path
            ? route('profile.photo.show', ['user' => $this->id, 'v' => substr(hash('sha256', $this->profile_photo_path), 0, 12)])
            : asset('images/default-avatar.svg');
    }
    // Role is deliberately excluded: public input cannot mass-assign privileges.
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['suspended_at' => 'datetime', 'password' => 'hashed', 'role' => Role::class];
    }
}
