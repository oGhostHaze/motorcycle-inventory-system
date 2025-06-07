<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class UserManagement extends Component
{
    use WithPagination;
    use Toast;

    public bool $showModal = false;
    public bool $editMode = false;
    public ?User $selectedUser = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $role = 'cashier';
    public bool $is_active = true;
    public array $permissions = [];
    public string $password = '';
    public string $password_confirmation = '';

    // Search and filters
    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|in:admin,manager,cashier,warehouse_staff',
        'is_active' => 'boolean',
        'password' => 'nullable|min:8|confirmed',
        'permissions' => 'array',
    ];

    public function mount()
    {
        $this->permissions = [];
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%'))
            ->when($this->roleFilter, fn($q) => $q->where('role', $this->roleFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $roles = [
            ['value' => 'admin', 'label' => 'Administrator'],
            ['value' => 'manager', 'label' => 'Manager'],
            ['value' => 'cashier', 'label' => 'Cashier'],
            ['value' => 'warehouse_staff', 'label' => 'Warehouse Staff'],
        ];

        $statusOptions = [
            ['value' => '', 'label' => 'All Status'],
            ['value' => '1', 'label' => 'Active'],
            ['value' => '0', 'label' => 'Inactive'],
        ];

        $roleOptions = [
            ['value' => '', 'label' => 'All Roles'],
            ...$roles
        ];

        $availablePermissions = [
            'manage_users' => 'Manage Users',
            'manage_inventory' => 'Manage Inventory',
            'process_sales' => 'Process Sales',
            'view_reports' => 'View Reports',
            'manage_suppliers' => 'Manage Suppliers',
            'manage_customers' => 'Manage Customers',
            'manage_products' => 'Manage Products',
            'manage_warehouses' => 'Manage Warehouses',
            'manage_settings' => 'Manage Settings',
            'view_analytics' => 'View Analytics',
        ];

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => $roles,
            'roleOptions' => $roleOptions,
            'statusOptions' => $statusOptions,
            'availablePermissions' => $availablePermissions,
        ]);
    }

    public function openModal()
    {
        $this->reset(['name', 'email', 'role', 'is_active', 'permissions', 'password', 'password_confirmation']);
        $this->editMode = false;
        $this->selectedUser = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function editUser(User $user)
    {
        $this->selectedUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->is_active = $user->is_active;
        $this->permissions = $user->permissions ?? [];
        $this->password = '';
        $this->password_confirmation = '';
        $this->editMode = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['email'] = 'required|email|unique:users,email,' . $this->selectedUser->id;
            $this->rules['password'] = 'nullable|min:8|confirmed';
        } else {
            $this->rules['password'] = 'required|min:8|confirmed';
        }

        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'is_active' => $this->is_active,
                'permissions' => $this->permissions,
            ];

            if (!empty($this->password)) {
                $data['password'] = bcrypt($this->password);
            }

            if ($this->editMode) {
                $this->selectedUser->update($data);
                $this->success('User updated successfully!');
            } else {
                User::create($data);
                $this->success('User created successfully!');
            }

            $this->showModal = false;
            $this->reset(['name', 'email', 'role', 'is_active', 'permissions', 'password', 'password_confirmation']);
        } catch (\Exception $e) {
            $this->error('Error saving user: ' . $e->getMessage());
        }
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->error('You cannot delete your own account!');
            return;
        }

        try {
            $user->delete();
            $this->success('User deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting user: ' . $e->getMessage());
        }
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->error('You cannot deactivate your own account!');
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        $this->success("User {$status} successfully!");
    }

    public function updatedRole()
    {
        $this->permissions = User::getDefaultPermissions($this->role);
    }

    public function clearFilters()
    {
        $this->reset(['search', 'roleFilter', 'statusFilter']);
    }
}
