<div>
    {{-- Page Header --}}
    <x-mary-header title="Category Management" subtitle="Manage product categories and subcategories" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search categories..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" @click="$wire.openModal()">
                Add Category
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-select placeholder="Filter by status" :options="$statusOptions" wire:model.live="statusFilter" />
        <div class="md:col-span-2 md:flex md:justify-end">
            <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
                Clear Filters
            </x-mary-button>
        </div>
    </div>

    {{-- Categories Grid --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($categories as $category)
            <x-mary-card class="h-full">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        @if ($category->icon)
                            <div class="p-2 rounded-lg bg-primary/10">
                                <x-icon name="{{ $category->icon }}" class="w-6 h-6 text-primary" />
                            </div>
                        @else
                            <div class="p-2 rounded-lg bg-primary/10">
                                <x-heroicon-o-tag class="w-6 h-6 text-primary" />
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold">{{ $category->name }}</h3>
                            <x-mary-badge value="{{ $category->is_active ? 'Active' : 'Inactive' }}"
                                class="badge-{{ $category->is_active ? 'success' : 'error' }} badge-sm" />
                        </div>
                    </div>

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                            <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                            <li><a wire:click="editCategory({{ $category->id }})"><x-heroicon-o-pencil
                                        class="w-4 h-4" /> Edit</a></li>
                            <li><a wire:click="openSubcategoryModal({{ $category->id }})"><x-heroicon-o-plus
                                        class="w-4 h-4" /> Add Subcategory</a></li>
                            <li><a wire:click="toggleCategoryStatus({{ $category->id }})">
                                    <x-heroicon-o-{{ $category->is_active ? 'x-mark' : 'check' }} class="w-4 h-4" />
                                    {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                </a></li>
                            <li><a wire:click="deleteCategory({{ $category->id }})" wire:confirm="Are you sure?"
                                    class="text-error">
                                    <x-heroicon-o-trash class="w-4 h-4" /> Delete
                                </a></li>
                        </ul>
                    </div>
                </div>

                @if ($category->description)
                    <p class="mb-4 text-sm text-gray-600">{{ $category->description }}</p>
                @endif

                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="p-3 text-center rounded-lg bg-info/10">
                        <div class="text-2xl font-bold text-info">{{ $category->products_count }}</div>
                        <div class="text-sm text-gray-600">Products</div>
                    </div>
                    <div class="p-3 text-center rounded-lg bg-secondary/10">
                        <div class="text-2xl font-bold text-secondary">{{ $category->subcategories_count }}</div>
                        <div class="text-sm text-gray-600">Subcategories</div>
                    </div>
                </div>

                {{-- Subcategories --}}
                @if ($category->subcategories->count() > 0)
                    <div class="space-y-2">
                        <h4 class="text-sm font-medium text-gray-700">Subcategories:</h4>
                        <div class="space-y-1">
                            @foreach ($category->subcategories->take(3) as $subcategory)
                                <div class="flex items-center justify-between p-2 rounded bg-base-200">
                                    <span class="text-sm">{{ $subcategory->name }}</span>
                                    <div class="flex gap-1">
                                        <x-mary-badge value="{{ $subcategory->is_active ? 'Active' : 'Inactive' }}"
                                            class="badge-{{ $subcategory->is_active ? 'success' : 'error' }} badge-xs" />
                                        <x-mary-button icon="o-pencil"
                                            wire:click="editSubcategory({{ $subcategory->id }})"
                                            class="btn-ghost btn-xs" />
                                        <x-mary-button icon="o-trash"
                                            wire:click="deleteSubcategory({{ $subcategory->id }})"
                                            wire:confirm="Are you sure?" class="btn-ghost btn-xs text-error" />
                                    </div>
                                </div>
                            @endforeach
                            @if ($category->subcategories->count() > 3)
                                <div class="text-xs text-center text-gray-500">
                                    +{{ $category->subcategories->count() - 3 }} more subcategories
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </x-mary-card>
        @empty
            <div class="col-span-full">
                <x-mary-card>
                    <div class="py-8 text-center">
                        <x-heroicon-o-tag class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No categories found</p>
                        <x-mary-button label="Create First Category" wire:click="openModal" class="mt-4 btn-primary" />
                    </div>
                </x-mary-card>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $categories->links() }}
    </div>

    {{-- Category Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Category' : 'Create New Category' }}"
        subtitle="Manage category information">

        <div class="space-y-4">
            <x-mary-input label="Category Name" wire:model="name" placeholder="Enter category name" />

            <x-mary-textarea label="Description" wire:model="description" placeholder="Category description (optional)"
                rows="3" />

            <div class="grid grid-cols-2 gap-3">
                <x-mary-input label="Icon Class" wire:model="icon" placeholder="e.g., o-cog-6-tooth" />
                <x-mary-input label="Sort Order" wire:model="sort_order" type="number" placeholder="0" />
            </div>

            <x-mary-checkbox label="Active Category" wire:model="is_active" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update Category' : 'Create Category' }}" wire:click="saveCategory"
                class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Subcategory Modal --}}
    <x-mary-modal wire:model="showSubcategoryModal"
        title="{{ $editMode ? 'Edit Subcategory' : 'Create New Subcategory' }}"
        subtitle="Manage subcategory for {{ $selectedCategory?->name }}">

        <div class="space-y-4">
            <x-mary-input label="Subcategory Name" wire:model="subcategory_name"
                placeholder="Enter subcategory name" />

            <x-mary-textarea label="Description" wire:model="subcategory_description"
                placeholder="Subcategory description (optional)" rows="3" />

            <div class="grid grid-cols-2 gap-3">
                <x-mary-input label="Sort Order" wire:model="subcategory_sort_order" type="number"
                    placeholder="0" />
                <div class="flex items-center">
                    <x-mary-checkbox label="Active Subcategory" wire:model="subcategory_is_active" />
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showSubcategoryModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update Subcategory' : 'Create Subcategory' }}"
                wire:click="saveSubcategory" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
