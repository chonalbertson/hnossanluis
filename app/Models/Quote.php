<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'nueva' => 'Nueva',
        'en_revision' => 'En revisión',
        'pendiente_info' => 'Pendiente de información',
        'en_preparacion' => 'En preparación',
        'respondida' => 'Respondida / Enviada',
        'aceptada' => 'Aceptada',
        'rechazada' => 'Rechazada',
        'cerrada' => 'Cerrada',
    ];

    protected $fillable = [
        'reference_code',
        'customer_id',
        'user_id',
        'assigned_seller_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'company_name',
        'ruc',
        'request_type',
        'origin',
        'status',
        'currency',
        'subtotal_offered',
        'tax_offered',
        'total_offered',
        'customer_observations',
        'commercial_response',
        'commercial_conditions',
        'validity_days',
        'valid_until',
        'privacy_accepted_at',
        'responded_at',
        'follow_up_date',
        'last_email_idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_offered' => 'decimal:2',
            'tax_offered' => 'decimal:2',
            'total_offered' => 'decimal:2',
            'validity_days' => 'integer',
            'valid_until' => 'date',
            'privacy_accepted_at' => 'datetime',
            'responded_at' => 'datetime',
            'follow_up_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedSeller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(QuoteStatusHistory::class)->orderByDesc('created_at');
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(InternalNote::class)->orderByDesc('is_pinned')->orderByDesc('created_at');
    }

    public static function availableStatuses(): array
    {
        return self::STATUSES;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'nueva' => 'bg-blue-100 text-blue-800 border-blue-200',
            'en_revision' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'pendiente_info' => 'bg-amber-100 text-amber-800 border-amber-200',
            'en_preparacion' => 'bg-purple-100 text-purple-800 border-purple-200',
            'respondida' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            'aceptada' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'rechazada' => 'bg-rose-100 text-rose-800 border-rose-200',
            'cerrada' => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function currencySymbol(): string
    {
        return match (strtoupper((string) $this->currency)) {
            'USD' => '$',
            'EUR' => '€',
            default => 'S/',
        };
    }
}
