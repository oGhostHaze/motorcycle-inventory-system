<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportsAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Allow access for admin and manager roles
        if (in_array($user->role, ['admin', 'manager'])) {
            return $next($request);
        }

        // For other roles, check specific permissions (if you have a permissions system)
        // You can customize this based on your role/permission structure
        if ($user->role === 'cashier' && $request->routeIs('reports.sales')) {
            // Allow cashiers to view only sales reports
            return $next($request);
        }

        // Deny access for unauthorized roles
        abort(403, 'You do not have permission to access reports.');
    }
}
