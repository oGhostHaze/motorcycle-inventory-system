<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has the specific permission or if their role grants access
        if (!$user->hasPermission($permission) && !$this->hasRoleBasedAccess($user, $permission)) {
            abort(403, 'Unauthorized. You do not have the required permission.');
        }

        return $next($request);
    }

    /**
     * Check if user's role grants access to the permission
     */
    private function hasRoleBasedAccess($user, $permission): bool
    {
        return match ($permission) {
            'manage_inventory' => $user->canManageInventory(),
            'process_sales' => $user->canProcessSales(),
            'view_reports' => $user->canViewReports(),
            'manage_users' => $user->canManageUsers(),
            default => false,
        };
    }
}
