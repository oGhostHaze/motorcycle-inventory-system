<div>
    {{-- Page Header --}}
    <x-mary-header title="User Management" subtitle="Manage system users and their permissions" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search users..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" @click="$wire.openModal()">
                Add User
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-select placeholder="Filter by role" :options="$roleOptions" wire:model.live="roleFilter" />
        <x-mary-select placeholder="Filter by status" :options="$statusOptions" wire:model.live="statusFilter" />
        <div class="md:col-span-2 md:flex md:justify-end">
            <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
                Clear Filters
            </x-mary-button>
        </div>
    </div>

    {{-- Users Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th class="w-32">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="w-8 h-8 text-xs rounded-full bg-neutral text-neutral-content">
                                            <span>{{ substr($user->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ $user->name }}</div>
                                        <div class="text-sm opacity-50">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ $user->role_display_name }}"
                                    class="badge-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'manager' ? 'secondary' : 'accent') }}" />
                            </td>
                            <td>
                                <x-mary-badge value="{{ $user->is_active ? 'Active' : 'Inactive' }}"
                                    class="badge-{{ $user->is_active ? 'success' : 'error' }}" />
                            </td>
                            <td>
                                <span class="text-sm">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-sm">{{ $user->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <x-mary-button icon="o-pencil" wire:click="editUser({{ $user->id }})"
                                        class="btn-ghost btn-xs" tooltip="Edit" />

                                    @if ($user->id !== auth()->id())
                                        <x-mary-button icon="o-{{ $user->is_active ? 'x-mark' : 'check' }}"
                                            wire:click="toggleStatus({{ $user->id }})" class="btn-ghost btn-xs"
                                            tooltip="{{ $user->is_active ? 'Deactivate' : 'Activate' }}" />

                                        <x-mary-button icon="o-trash" wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete this user?"
                                            class="btn-ghost btn-xs text-error" tooltip="Delete" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-users class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No users found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-mary-card>

    {{-- Create/Edit Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit User' : 'Create New User' }}"
        subtitle="Manage user account and permissions">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-mary-input label="Full Name" wire:model="name" placeholder="Enter full name" />
            <x-mary-input label="Email Address" wire:model="email" placeholder="Enter email address" />

            <x-mary-select label="Role" :options="$roles" wire:model.live="role" placeholder="Select role" />

            <div class="flex items-center gap-2">
                <x-mary-checkbox label="Active User" wire:model="is_active" />
            </div>
        </div>

        <div class="mt-4">
            <x-mary-input label="Password" wire:model="password" type="password"
                placeholder="{{ $editMode ? 'Leave blank to keep current password' : 'Enter password' }}" />
        </div>

        <div class="mt-4">
            <x-mary-input label="Confirm Password" wire:model="password_confirmation" type="password"
                placeholder="Confirm password" />
        </div>

        {{-- Permissions --}}
        <div class="mt-6">
            <h4 class="mb-3 text-lg font-semibold">Permissions</h4>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                @foreach ($availablePermissions as $permission => $label)
                    <x-mary-checkbox label="{{ $label }}" wire:model="permissions"
                        value="{{ $permission }}" />
                @endforeach
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update User' : 'Create User' }}" wire:click="save"
                class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
