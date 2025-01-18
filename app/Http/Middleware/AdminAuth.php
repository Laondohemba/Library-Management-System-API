<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = JWTAuth::getToken();
        if(!$token || !JWTAuth::setToken($token)->check() )
        {
            return response()->json([
                'errors' => 'This action is unauthorized'
            ], 401);
        }

        $admin = auth('admin')->user();

        if(!$admin || $admin->is_admin != true)
        {
            return response()->json([
                'errors' => 'This action is forbidden for the current user',
            ], 403);
        }
        return $next($request);
    }
}
