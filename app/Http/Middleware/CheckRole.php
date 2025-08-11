<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            Log::warning('CheckRole: No authenticated user');
            abort(403, 'Unauthorized - No authenticated user');
        }

        $userRole = $request->user()->role;
        
        // $roles is now an array of allowed roles from the route definition.
        $allowedRoles = $roles;
        
        // Debug logging
        Log::info('CheckRole middleware', [
            'user_id' => $request->user()->id,
            'user_role' => $userRole,
            'allowed_roles' => $allowedRoles,
            'url' => $request->url()
        ]);
        
        // Check if user's role is in the allowed roles (case-insensitive check)
        $userRoleLower = strtolower($userRole);
        $allowedRolesLower = array_map('strtolower', $allowedRoles);
        
        if (!in_array($userRoleLower, $allowedRolesLower)) {
            Log::warning('CheckRole: Access denied', [
                'user_role' => $userRole,
                'user_role_lower' => $userRoleLower,
                'allowed_roles' => $allowedRoles,
                'allowed_roles_lower' => $allowedRolesLower
            ]);
            abort(403, "Access denied. Your role '{$userRole}' is not authorized. Allowed roles: " . implode(', ', $allowedRoles));
        }

        Log::info('CheckRole: Access granted', ['user_role' => $userRole]);
        return $next($request);
    }
} 