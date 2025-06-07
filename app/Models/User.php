<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // Inventory System Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'requested_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function cycleCounts()
    {
        return $this->hasMany(CycleCount::class, 'initiated_by');
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'initiated_by');
    }

    // Role and Permission Methods
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function isCashier()
    {
        return $this->role === 'cashier';
    }

    public function isWarehouseStaff()
    {
        return $this->role === 'warehouse_staff';
    }

    // Helper method to get role display name
    public function getRoleDisplayNameAttribute()
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'cashier' => 'Cashier',
            'warehouse_staff' => 'Warehouse Staff',
            default => ucfirst($this->role),
        };
    }

    // Get default permissions for role
    public static function getDefaultPermissions($role)
    {
        return match ($role) {
            'admin' => [
                'manage_users',
                'manage_inventory',
                'process_sales',
                'view_reports',
                'manage_suppliers',
                'manage_customers',
                'manage_products',
                'manage_warehouses',
                'manage_settings',
                'view_analytics'
            ],
            'manager' => [
                'manage_inventory',
                'process_sales',
                'view_reports',
                'manage_suppliers',
                'manage_customers',
                'manage_products',
                'view_analytics'
            ],
            'cashier' => [
                'process_sales',
                'view_products',
                'manage_customers'
            ],
            'warehouse_staff' => [
                'manage_inventory',
                'view_products',
                'process_receiving',
                'manage_stock_transfers'
            ],
            default => [],
        };
    }


    public function canManageInventory()
    {
        return in_array($this->role, ['admin', 'manager', 'warehouse_staff']) ||
            $this->hasPermission('manage_inventory');
    }

    public function canProcessSales()
    {
        return in_array($this->role, ['admin', 'manager', 'cashier']) ||
            $this->hasPermission('process_sales');
    }

    public function canViewReports()
    {
        return in_array($this->role, ['admin', 'manager']) ||
            $this->hasPermission('view_reports');
    }

    public function canManageUsers()
    {
        return $this->role === 'admin' || $this->hasPermission('manage_users');
    }

    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }
}
