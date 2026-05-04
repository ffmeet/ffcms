<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class FrontendCache
{
    public const VERSION_KEY = 'frontend.cache.version';

    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    public static function bump(): int
    {
        $next = static::version() + 1;
        Cache::forever(self::VERSION_KEY, $next);

        return $next;
    }

    public static function flushAll(): int
    {
        SiteSetting::flushCurrentCache();

        return static::bump();
    }

    public static function key(string $segment, array $parts = []): string
    {
        $suffix = $parts === [] ? '' : '.'.md5(json_encode($parts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return 'frontend.'.$segment.'.v'.static::version().$suffix;
    }
}
