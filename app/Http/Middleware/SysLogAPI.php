<?php

namespace App\Http\Middleware;

use App\Models\SysAPILog;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Log request and response API
 */
class SysLogAPI
{

    protected $maskAttributes = [
        'password',
        'password_confirmation'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $response = $next($request);

        // dont log if config or called from Backend site
        if (!env('API_DEBUG', false) || $request->server('HTTP_X_AMSS_BE')) {
            return $response;
        }

        try {
            SysAPILog::create([
                'method' => $request->getMethod(),
                'url' => $request->fullUrl(),
                'request' => $request->getContent(),
                'response' => $response->getContent(),
                'host_ip' => $request->ip(),
                'user_agent' => $request->server('HTTP_USER_AGENT'),
                'request_date' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $exception) {
            Log::error('[SysLogAPI] Middleware failed to store data.', ['ex' => $exception]);
        }

        return $response;
    }
}
