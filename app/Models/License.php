<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'encrypted_key',
        'renewal_date',
        'expiry_date'
    ];

    protected $dates = [
        'renewal_date',
        'expiry_date'
    ];

    // Helper method to check if license is expired
    public function isExpired()
    {
        return Carbon::now()->gt(Carbon::parse($this->expiry_date));
    }

    // Helper method to check if license is in grace period
    public function isInGracePeriod()
    {
        $expiry = Carbon::parse($this->expiry_date);
        $graceEnd = $expiry->copy()->addDays(2);
        $now = Carbon::now();
        
        return $now->gt($expiry) && $now->lte($graceEnd);
    }

    // Helper method to check if grace period has ended
    public function isGracePeriodEnded()
    {
        $expiry = Carbon::parse($this->expiry_date);
        $graceEnd = $expiry->copy()->addDays(2);
        
        return Carbon::now()->gt($graceEnd);
    }
    public function isAboutToExpire($days = 3)
    {
        $now = Carbon::now()->startOfDay();
        $expiryDate = Carbon::parse($this->expiry_date)->startOfDay();
        $daysUntilExpiry = $now->diffInDays($expiryDate, false);
        
        return $daysUntilExpiry >= 0 && $daysUntilExpiry <= $days;
    }
}