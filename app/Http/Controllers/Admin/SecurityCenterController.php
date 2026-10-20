<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityIncident;
use App\Models\SecurityScan;
use App\Services\ActivityLoggerService;
use App\Services\MalwareScannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SecurityCenterController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $latestScan = SecurityScan::query()
            ->with('triggeredBy')
            ->latest()
            ->first();

        $kpis = [
            'waf_active' => true,
            'scanner_active' => true,
            'total_incidents' => SecurityIncident::query()->count(),
            'pending_incidents' => SecurityIncident::query()
                ->whereIn('status', ['blocked', 'quarantined'])
                ->count(),
            'critical_incidents' => SecurityIncident::query()
                ->where('severity', 'critical')
                ->whereIn('status', ['blocked', 'quarantined'])
                ->count(),
            'total_critical_incidents' => SecurityIncident::query()
                ->where('severity', 'critical')
                ->count(),
            'quarantined_files' => SecurityIncident::query()
                ->whereNotNull('quarantine_path')
                ->count(),
            'resolved_incidents' => SecurityIncident::query()
                ->whereIn('status', ['resolved', 'false_positive'])
                ->count(),
        ];

        $incidentsQuery = SecurityIncident::query()->with(['user', 'resolver']);

        if ($request->filled('threat_type')) {
            $incidentsQuery->where('threat_type', $request->string('threat_type')->toString());
        }

        if ($request->filled('severity')) {
            $incidentsQuery->where('severity', $request->string('severity')->toString());
        }

        if ($request->filled('status')) {
            $incidentsQuery->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $incidentsQuery->where(function ($query) use ($term): void {
                $query->where('ip_address', 'like', $term)
                    ->orWhere('file_name', 'like', $term)
                    ->orWhere('matched_rule', 'like', $term)
                    ->orWhere('file_hash', 'like', $term)
                    ->orWhere('url', 'like', $term);
            });
        }

        $incidents = $incidentsQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $recentScans = SecurityScan::query()
            ->with('triggeredBy')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $threatTypes = [
            'malware_upload' => 'Subida de Malware / Ejecutable',
            'webshell_signature' => 'Firma de Webshell / Backdoor',
            'pdf_exploit' => 'Exploit en Documento PDF',
            'csv_formula_injection' => 'Inyección de Fórmulas CSV / DDE',
            'path_traversal' => 'Path Traversal / Acceso de Directorios',
            'code_injection' => 'Inyección de Código Remoto (RCE)',
            'xss_attack' => 'Ataque Cross-Site Scripting (XSS)',
            'unauthorized_script' => 'Script No Autorizado en Storage',
        ];

        return view('admin.security.index', [
            'kpis' => $kpis,
            'latestScan' => $latestScan,
            'incidents' => $incidents,
            'recentScans' => $recentScans,
            'threatTypes' => $threatTypes,
        ]);
    }

    public function scan(Request $request, MalwareScannerService $scanner): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $scan = $scanner->scanStorageDirectory($request->user(), 'storage_scan', true);

        if ($scan->threats_found > 0) {
            return redirect()
                ->route('admin.security.index')
                ->with(
                    'warning',
                    sprintf(
                        'Alerta de Seguridad: Escaneo completado en %d ms (%d archivos analizados). Se detectaron %d amenaza(s) y %d archivo(s) fueron aislados en cuarentena.',
                        $scan->duration_ms,
                        $scan->files_scanned,
                        $scan->threats_found,
                        $scan->quarantined_count
                    )
                );
        }

        return redirect()
            ->route('admin.security.index')
            ->with(
                'success',
                sprintf(
                    'Escaneo Antimalware completado con éxito en %d ms: %d archivos verificados en almacenamiento público. No se detectaron amenazas (0 infecciones).',
                    $scan->duration_ms,
                    $scan->files_scanned
                )
            );
    }

    public function showIncident(Request $request, SecurityIncident $incident): View
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $incident->load(['user', 'resolver']);

        $quarantineFileExists = false;
        $quarantineFullPath = null;

        if (! empty($incident->quarantine_path)) {
            $safeFilename = basename($incident->quarantine_path);
            $candidatePath = storage_path('app/quarantine/'.$safeFilename);
            if (File::exists($candidatePath)) {
                $quarantineFileExists = true;
                $quarantineFullPath = $candidatePath;
            }
        }

        return view('admin.security.show', [
            'incident' => $incident,
            'quarantineFileExists' => $quarantineFileExists,
            'quarantineFullPath' => $quarantineFullPath,
        ]);
    }

    public function resolveIncident(Request $request, SecurityIncident $incident): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:resolved,false_positive'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'status.required' => 'Debe seleccionar un estado de resolución válido.',
            'status.in' => 'El estado seleccionado debe ser Resuelto o Falso Positivo.',
            'resolution_notes.max' => 'Las notas de resolución no deben exceder los 2000 caracteres.',
        ]);

        $previousStatus = $incident->status;

        $incident->update([
            'status' => $validated['status'],
            'resolved_by' => $request->user()?->id,
            'resolved_at' => now(),
            'resolution_notes' => $validated['resolution_notes'] ?? $incident->resolution_notes,
        ]);

        $this->activityLogger->log(
            'security.incident_resolved',
            'security',
            sprintf(
                'Incidente de seguridad #%d (%s) marcado como "%s".',
                $incident->id,
                $incident->threat_type,
                $incident->status_label
            ),
            $incident,
            [
                'previous_status' => $previousStatus,
                'new_status' => $incident->status,
                'resolution_notes' => $incident->resolution_notes,
            ]
        );

        return redirect()
            ->route('admin.security.incidents.show', $incident)
            ->with('success', sprintf('El incidente de seguridad #%d ha sido actualizado a "%s".', $incident->id, $incident->status_label));
    }

    public function deleteQuarantine(Request $request, SecurityIncident $incident): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $previousQuarantinePath = $incident->quarantine_path;
        if (empty($previousQuarantinePath)) {
            return redirect()
                ->route('admin.security.incidents.show', $incident)
                ->with('warning', 'Este incidente no tiene ningún archivo activo en cuarentena.');
        }

        $safeFilename = basename($previousQuarantinePath);
        $fullPath = storage_path('app/quarantine/'.$safeFilename);

        $deletedFromDisk = false;
        if (File::exists($fullPath)) {
            $deletedFromDisk = File::delete($fullPath);
        }

        $purgeNote = sprintf(
            '[%s] Archivo en cuarentena (%s) eliminado permanentemente por %s.',
            now()->format('d/m/Y H:i:s'),
            $safeFilename,
            $request->user()?->name ?? 'Administrador'
        );

        $updatedNotes = trim(($incident->resolution_notes ? $incident->resolution_notes."\n" : '').$purgeNote);

        $updateData = [
            'quarantine_path' => null,
            'resolution_notes' => $updatedNotes,
        ];

        if ($incident->status === 'quarantined') {
            $updateData['status'] = 'resolved';
            $updateData['resolved_by'] = $request->user()?->id;
            $updateData['resolved_at'] = now();
        }

        $incident->update($updateData);

        $this->activityLogger->log(
            'security.quarantine_deleted',
            'security',
            sprintf(
                'Archivo en cuarentena "%s" del incidente #%d eliminado permanentemente del servidor.',
                $safeFilename,
                $incident->id
            ),
            $incident,
            [
                'quarantine_file' => $safeFilename,
                'deleted_from_disk' => $deletedFromDisk,
                'file_hash' => $incident->file_hash,
            ]
        );

        return redirect()
            ->route('admin.security.incidents.show', $incident)
            ->with('success', sprintf('El archivo en cuarentena "%s" fue eliminado permanentemente del servidor.', $safeFilename));
    }
}
