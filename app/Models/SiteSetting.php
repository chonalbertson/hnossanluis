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

    public const ALL_CACHE_KEY = 'site_settings:all_records';

    /**
     * @var array<string, self>|null
     */
    protected static ?array $memoizedAll = null;

    protected static ?int $memoizedAppId = null;

    protected $fillable = [
        'setting_group',
        'setting_key',
        'setting_value',
        'setting_type',
        'label',
        'is_public',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $setting): void {
            self::clearCache($setting->setting_key);
        });

        static::deleted(function (self $setting): void {
            self::clearCache($setting->setting_key);
        });
    }

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $currentAppId = spl_object_id(app());
            if (self::$memoizedAppId !== $currentAppId) {
                self::$memoizedAll = null;
                self::$memoizedAppId = $currentAppId;
            }

            self::$memoizedAll ??= Cache::remember(
                self::ALL_CACHE_KEY,
                3600,
                fn (): array => self::query()
                    ->get(['setting_key', 'setting_value', 'setting_type'])
                    ->keyBy('setting_key')
                    ->all()
            );

            if (! isset(self::$memoizedAll[$key])) {
                return $default;
            }

            $setting = self::$memoizedAll[$key];

            return self::castSettingValue($setting->setting_value, (string) $setting->setting_type);
        } catch (Throwable) {
            return $default;
        }
    }

    public static function clearCache(?string $key = null): void
    {
        self::$memoizedAll = null;

        try {
            Cache::forget(self::ALL_CACHE_KEY);
            if ($key !== null) {
                Cache::forget(self::CACHE_PREFIX.$key);
            }
        } catch (Throwable) {
            // Ignore cache driver issues during CLI/migrations
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

        self::clearCache($key);

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
