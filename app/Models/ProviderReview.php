<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProviderReview extends Model
{
    protected $fillable = ['rating', 'comment'];
    protected function casts(): array { return ['rating' => 'integer']; }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
}
