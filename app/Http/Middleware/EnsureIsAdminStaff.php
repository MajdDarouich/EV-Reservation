<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdminStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasAnyRole(['support staff', 'station manager', 'Super Admin'])) {
            return response()->json([
                'message' => 'Unauthorized. Admin staff access only.',
            ], 403);
        }

        return $next($request);
    }
}
