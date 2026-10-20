<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SiteSetting extends Model
{
    use HasFactory;

    public const CACHE_PREFIX = 'site_setting:';

    protected $fillable = [
        'setting_group',
        'setting_key',
        'setting_value',
        'setting_type',
        'label',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $setting = Cache::remember(self::CACHE_PREFIX.$key, 3600, function () use ($key) {
                return self::query()->where('setting_key', $key)->first();
            });

            if (! $setting) {
                return $default;
            }

            return self::castSettingValue($setting->setting_value, $setting->setting_type);
        } catch (Throwable) {
            return $default;
        }
    }

    public static function setValue(
        string $key,
        mixed $value,
        string $group = 'general',
        ?string $type = null,
        ?string $label = null,
        bool $isPublic = true
    ): self {
        $resolvedType = $type ?? match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'json',
            default => 'string',
        };

        $storedValue = match ($resolvedType) {
            'boolean' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE),
            default => $value !== null ? (string) $value : null,
        };

        $attributes = [
            'setting_group' => $group,
            'setting_value' => $storedValue,
            'setting_type' => $resolvedType,
            'is_public' => $isPublic,
        ];

        if ($label !== null) {
            $attributes['label'] = $label;
        }

        $setting = self::query()->updateOrCreate(
            ['setting_key' => $key],
            $attributes
        );

        try {
            Cache::forget(self::CACHE_PREFIX.$key);
        } catch (Throwable) {
            // Ignore cache driver issues during CLI/migrations
        }

        return $setting;
    }

    protected static function castSettingValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true),
            'integer' => (int) $value,
            'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }
}
