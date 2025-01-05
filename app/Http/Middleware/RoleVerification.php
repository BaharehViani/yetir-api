<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleVerification
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        if (!$request->user()) {
            return response([
                'status' => 'FAILED',
                'message' => 'UNAUTHORIZED'
            ])->setStatusCode(403);
        }

        $rolesExploded = explode('|', $roles);

        if (!in_array($request->user()->role, $rolesExploded)) {
            return response([
                'status' => 'FAILED',
                'message' => 'UNAUTHORIZED'
            ])->setStatusCode(403);
        }

        return $next($request);
    }
}
