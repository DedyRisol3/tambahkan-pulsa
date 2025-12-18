<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PulsaRedemption extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'provider',
        'points_used',
        'pulsa_amount',
        'status',
        'admin_notes',
        'processed_at'
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Daftar paket pulsa yang tersedia
     * Format: points => pulsa_amount (Rp)
     */
    public static function getPackages(): array
    {
        return [
            100 => 5000,      // 100 poin = Rp 5.000
            200 => 10000,     // 200 poin = Rp 10.000
            400 => 25000,     // 400 poin = Rp 25.000
            750 => 50000,     // 750 poin = Rp 50.000
            1500 => 100000,   // 1500 poin = Rp 100.000
        ];
    }

    /**
     * Daftar provider yang tersedia
     */
    public static function getProviders(): array
    {
        return [
            'telkomsel' => 'Telkomsel',
            'xl' => 'XL Axiata',
            'indosat' => 'Indosat Ooredoo',
            'tri' => 'Tri (3)',
            'smartfren' => 'Smartfren',
            'axis' => 'Axis',
        ];
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

