<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'username',
        'password',
        'email',
        'is_active',
        'last_login_at',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Automatically hash password when setting
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Check if admin is active
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * Scope for active admins only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}