<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
class ServiceCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'provider_services', 'service_category_id', 'provider_id')->withTimestamps();
    }
}
