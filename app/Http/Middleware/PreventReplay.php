<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PreventReplay
{
    public function handle(Request $request, Closure $next)
    {
        $jwt = (string) $request->query('jwt', '');
        if ($jwt === '') {
            return $next($request);
        }

        // SECURITY: gunakan hash jwt sebagai nonce key; TTL singkat.
        $key = 'tte:replay:' . hash('sha256', $jwt);
        $ttl = (int) config('tte.security.replay_nonce_ttl_seconds');

        if (Cache::has($key)) {
            abort(429, 'Replay detected.');
        }

        Cache::put($key, 1, $ttl);

        return $next($request);
    }
}