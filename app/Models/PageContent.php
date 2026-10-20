<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_key',
        'title',
        'subtitle',
        'content',
        'sections_json',
        'meta_title',
        'meta_description',
        'is_indexable',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sections_json' => 'array',
            'is_indexable' => 'boolean',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
