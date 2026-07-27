<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static bool $tableChecked = false;

    /**
     * Ensure the settings table exists (auto-runs migration if missing).
     */
    public static function resetTableCheck(): void
    {
        static::$tableChecked = false;
    }

    public static function ensureTable(): void
    {
        if (static::$tableChecked) {
            return;
        }

        try {
            static::count();
        } catch (\Throwable $e) {
            Artisan::call('migrate', ['--force' => true]);
        }

        static::$tableChecked = true;
    }

    public static function get(string $key, $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
