<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'triggered_by',
        'scan_type',
        'status',
        'files_scanned',
        'threats_found',
        'quarantined_count',
        'duration_ms',
        'findings',
    ];

    protected function casts(): array
    {
        return [
            'files_scanned' => 'integer',
            'threats_found' => 'integer',
            'quarantined_count' => 'integer',
            'duration_ms' => 'integer',
            'findings' => 'array',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function getScanTypeLabelAttribute(): string
    {
        return match ($this->scan_type) {
            'storage_scan' => 'Escaneo de Almacenamiento Público',
            'full_scan' => 'Escaneo Completo de Integridad',
            'scheduled_scan' => 'Escaneo Programado Automático',
            default => ucfirst(str_replace('_', ' ', (string) $this->scan_type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'running' => 'En Ejecución',
            'clean' => 'Limpio (Sin Amenazas)',
            'threats_found' => 'Amenazas Detectadas',
            'failed' => 'Fallido',
            'completed' => 'Completado',
            default => ucfirst((string) $this->status),
        };
    }
}
