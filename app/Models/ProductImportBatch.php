<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'status',
        'total_rows',
        'to_create_count',
        'to_update_count',
        'created_count',
        'updated_count',
        'rejected_count',
        'preview_rows',
        'error_rows',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'to_create_count' => 'integer',
            'to_update_count' => 'integer',
            'created_count' => 'integer',
            'updated_count' => 'integer',
            'rejected_count' => 'integer',
            'preview_rows' => 'array',
            'error_rows' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
