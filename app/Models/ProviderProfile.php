<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProviderProfile extends Model
{
    protected $fillable = ['phone', 'service_area', 'biography', 'experience_years', 'working_hours', 'is_available', 'is_working'];
    protected function casts(): array { return ['is_available' => 'boolean', 'is_working' => 'boolean', 'experience_years' => 'integer']; }
    public function contactNumber(): ?string
    {
        $raw = trim($this->phone ?? '');
        $digits = preg_replace('/\D/', '', $raw);
        // Sri Lankan local numbers use 0; international links use country code 94.
        if (preg_match('/^0[1-9][0-9]{8}$/', $digits)) {
            return '94'.substr($digits, 1);
        }
        if (str_starts_with($raw, '00')) {
            $digits = substr($digits, 2);
        } elseif (!str_starts_with($raw, '+') && !preg_match('/^94[1-9][0-9]{8}$/', $digits)) {
            return null;
        }
        return preg_match('/^[1-9][0-9]{7,14}$/', $digits) ? $digits : null;
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
