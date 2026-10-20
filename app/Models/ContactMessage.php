<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'ruc',
        'subject',
        'message',
        'status',
        'privacy_accepted_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'privacy_accepted_at' => 'datetime',
        ];
    }
}
