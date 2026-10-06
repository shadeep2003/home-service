<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoginChallenge extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = ['otp_hash', 'binding_hash', 'account_hash'];
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'resend_at' => 'datetime', 'pending_until' => 'datetime', 'consumed_at' => 'datetime'];
    }
}
