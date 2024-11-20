<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'phone',
        'last_whatsapp_at',
        'memory',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminder_at' => 'datetime',
            'memory' => 'json',
        ];
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}
