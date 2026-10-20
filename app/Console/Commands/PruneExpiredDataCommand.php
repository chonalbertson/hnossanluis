<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\ProductImportBatch;
use App\Services\ActivityLoggerService;
use Illuminate\Console\Command;

class PruneExpiredDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanluis:prune-data {--days=365 : Antigüedad en días para depurar logs y mensajes cerrados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplica la política de retención de datos (Ley N° 29733 / Seguridad Operativa) depurando logs antiguos y mensajes cerrados.';

    /**
     * Execute the console command.
     */
    public function handle(ActivityLoggerService $activityLogger): int
    {
        $days = max(30, (int) $this->option('days'));
        $cutoffDate = now()->subDays($days);

        $prunedLogs = ActivityLog::query()
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $prunedMessages = ContactMessage::query()
            ->whereIn('status', ['closed', 'archived', 'resolved', 'attended', 'read', 'leido', 'respondido'])
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $prunedPreviewBatches = ProductImportBatch::query()
            ->where('status', 'preview')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        $activityLogger->log(
            'system.data_pruned',
            'audit',
            sprintf(
                'Política de retención ejecutada (> %d días): %d logs de auditoría, %d mensajes cerrados y %d lotes en vista previa depurados.',
                $days,
                $prunedLogs,
                $prunedMessages,
                $prunedPreviewBatches
            ),
            null,
            [
                'retention_days' => $days,
                'cutoff_date' => $cutoffDate->toDateTimeString(),
                'pruned_activity_logs' => $prunedLogs,
                'pruned_contact_messages' => $prunedMessages,
                'pruned_preview_batches' => $prunedPreviewBatches,
            ]
        );

        $this->info(sprintf(
            'Política de retención aplicada (> %d días): %d logs, %d mensajes de contacto cerrados y %d lotes temporales eliminados.',
            $days,
            $prunedLogs,
            $prunedMessages,
            $prunedPreviewBatches
        ));

        return self::SUCCESS;
    }
}
