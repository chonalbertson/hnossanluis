<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'threat_type',
        'severity',
        'status',
        'ip_address',
        'user_id',
        'url',
        'http_method',
        'file_name',
        'file_hash',
        'file_size',
        'matched_rule',
        'details',
        'quarantine_path',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'file_size' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getThreatTypeLabelAttribute(): string
    {
        return match ($this->threat_type) {
            'malware_upload' => 'Subida de Malware / Ejecutable',
            'webshell_signature' => 'Firma de Webshell / Backdoor',
            'pdf_exploit' => 'Exploit en Documento PDF',
            'csv_formula_injection' => 'Inyección de Fórmulas CSV / DDE',
            'path_traversal' => 'Path Traversal / Acceso de Directorios',
            'code_injection' => 'Inyección de Código Remoto (RCE)',
            'xss_attack' => 'Ataque Cross-Site Scripting (XSS)',
            'unauthorized_script' => 'Script No Autorizado en Storage',
            default => ucfirst(str_replace('_', ' ', (string) $this->threat_type)),
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'Crítica',
            'high' => 'Alta',
            'medium' => 'Media',
            'low' => 'Baja',
            default => ucfirst((string) $this->severity),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'blocked' => 'Bloqueado',
            'quarantined' => 'En Cuarentena',
            'resolved' => 'Resuelto',
            'false_positive' => 'Falso Positivo',
            default => ucfirst((string) $this->status),
        };
    }
}
