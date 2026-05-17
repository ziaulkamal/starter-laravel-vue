<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfig extends Model
{
    protected $table = 'system_configs';

    protected $fillable = ['key', 'value'];

    /** Ambil nilai config, dengan cache Redis 1 jam */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("sys_config:{$key}", 3600, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /** Set nilai config dan bersihkan cache */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("sys_config:{$key}");
    }
}
