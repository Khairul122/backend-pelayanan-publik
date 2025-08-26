<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    protected $fillable = [
        'user_id',
        'aktivitas',
        'ip_address',
        'waktu',
        'status',
    ];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}