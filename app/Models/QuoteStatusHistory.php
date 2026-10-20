<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'changed_by',
        'previous_status',
        'new_status',
        'comment',
        'notified_customer',
    ];

    protected function casts(): array
    {
        return [
            'notified_customer' => 'boolean',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
