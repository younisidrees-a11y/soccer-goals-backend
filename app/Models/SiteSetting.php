<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Single-row settings table (always id=1). Currently holds the raw
 * analytics/tracking scripts an admin pastes in - Google Analytics, Google
 * Tag Manager, Meta Pixel, etc. - which get injected into every public page
 * without needing a code deploy.
 */
class SiteSetting extends Model
{
    protected $fillable = ['analytics_head_code', 'analytics_body_code'];

    public static function current(): self
    {
        $attributes = Cache::rememberForever(
            'site-settings',
            fn () => self::firstOrCreate(['id' => 1])->only(['analytics_head_code', 'analytics_body_code'])
        );

        return new self($attributes);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site-settings'));
    }
}
