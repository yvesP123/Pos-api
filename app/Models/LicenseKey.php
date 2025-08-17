<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class LicenseKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'company_name',
        'encrypted_key',
        'plain_key',
        'issue_date',
        'expiry_date',
        'duration_days',
        'status',
        'generated_by',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'issue_date' => 'date',
        'expiry_date' => 'date'
    ];

    public static function generateKey($companyId, $expiryDate, $durationDays)
    {
        $plainKey = [
            'company_id' => $companyId,
            'expiry_date' => $expiryDate,
            'duration' => $durationDays,
            'generated_at' => now()->timestamp,
            'random' => bin2hex(random_bytes(16))
        ];

        $plainKeyString = base64_encode(json_encode($plainKey));
        $encryptedKey = Crypt::encryptString($plainKeyString);

        return [
            'plain_key' => $plainKeyString,
            'encrypted_key' => $encryptedKey
        ];
    }

    public function isExpired()
    {
        return $this->expiry_date->isPast();
    }

    public function daysRemaining()
    {
        return $this->expiry_date->diffInDays(now(), false);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }
}