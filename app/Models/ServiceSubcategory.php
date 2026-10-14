<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServiceSubcategory extends Model
{
    use \App\Models\Concerns\HasServiceImage;
    protected $fillable = ['name', 'description', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function category() { return $this->belongsTo(ServiceCategory::class, 'service_category_id'); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function providers() { return $this->belongsToMany(User::class, 'provider_subcategories', 'service_subcategory_id', 'provider_id')->withTimestamps(); }
}
