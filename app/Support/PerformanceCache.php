<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class PerformanceCache
{
    public static function remember(string $namespace, string $suffix, int $ttlSeconds, Closure $callback): mixed
    {
        $version = self::namespaceVersion($namespace);
        $key = self::cacheKey($namespace, $version, $suffix);

        return Cache::remember($key, now()->addSeconds(max(1, $ttlSeconds)), $callback);
    }

    public static function invalidate(string $namespace): void
    {
        $versionKey = self::versionKey($namespace);
        $nextVersion = self::namespaceVersion($namespace) + 1;

        Cache::forever($versionKey, $nextVersion);
    }

    private static function namespaceVersion(string $namespace): int
    {
        return (int) Cache::get(self::versionKey($namespace), 1);
    }

    private static function cacheKey(string $namespace, int $version, string $suffix): string
    {
        return sprintf('perf:%s:v%d:%s', $namespace, $version, $suffix);
    }

    private static function versionKey(string $namespace): string
    {
        return sprintf('perf:%s:version', $namespace);
    }
}

