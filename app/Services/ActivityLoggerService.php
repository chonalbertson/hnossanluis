<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLoggerService
{
    /**
     * Sensitive keys that must never be stored in audit logs.
     *
     * @var array<int, string>
     */
    protected array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'remember_token',
        'secret',
        'api_key',
        'smtp_password',
        'mail_password',
        'whatsapp_api_token',
    ];

    public function log(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?int $userId = null
    ): ActivityLog {
        $sanitizedProperties = $this->sanitizeProperties($properties);

        return ActivityLog::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => empty($sanitizedProperties) ? null : $sanitizedProperties,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    protected function sanitizeProperties(array $properties): array
    {
        $cleaned = [];

        foreach ($properties as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            $isSensitive = false;
            foreach ($this->sensitiveKeys as $sensitive) {
                if (str_contains($normalizedKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $cleaned[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $cleaned[$key] = $this->sanitizeProperties($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }
}
