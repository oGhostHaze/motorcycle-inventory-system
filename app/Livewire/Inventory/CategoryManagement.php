<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Subcategory;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class CategoryManagement extends Component
{
    use WithPagination;
    use Toast;

    public bool $showModal = false;
    public bool $showSubcategoryModal = false;
    public bool $editMode = false;
    public ?Category $selectedCategory = null;
    public ?Subcategory $selectedSubcategory = null;

    // Category form fields
    public string $name = '';
    public string $description = '';
    public string $icon = '';
    public $sort_order = '';
    public bool $is_active = true;

    // Subcategory form fields
    public string $subcategory_name = '';
    public string $subcategory_description = '';
    public $subcategory_sort_order = '';
    public bool $subcategory_is_active = true;
    public $category_id = '';

    // Search and filters
    public string $search = '';
    public string $statusFilter = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:100',
        'sort_order' => 'nullable|integer|min:0',
        'is_active' => 'boolean',
    ];

    protected array $subcategoryRules = [
        'subcategory_name' => 'required|string|max:255',
        'subcategory_description' => 'nullable|string',
        'subcategory_sort_order' => 'nullable|integer|min:0',
        'subcategory_is_active' => 'boolean',
        'category_id' => 'required|exists:categories,id',
    ];

    public function render()
    {
        $categories = Category::withCount(['products', 'subcategories'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        $statusOptions = [
            ['value' => '', 'label' => 'All Status'],
            ['value' => '1', 'label' => 'Active'],
            ['value' => '0', 'label' => 'Inactive'],
        ];

        return view('livewire.inventory.category-management', [
            'categories' => $categories,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function openModal()
    {
        $this->resetCategoryForm();
        $this->editMode = false;
        $this->selectedCategory = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function editCategory(Category $category)
    {
        $this->selectedCategory = $category;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->icon = $category->icon ?? '';
        $this->sort_order = $category->sort_order;
        $this->is_active = $category->is_active;
        $this->editMode = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function saveCategory()
    {
        $this->validate($this->rules);

        try {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'icon' => $this->icon,
                'sort_order' => $this->sort_order ?? 0,
                'is_active' => $this->is_active,
            ];

            if ($this->editMode) {
                $this->selectedCategory->update($data);
                $this->success('Category updated successfully!');
            } else {
                Category::create($data);
                $this->success('Category created successfully!');
            }

            $this->showModal = false;
            $this->resetCategoryForm();
        } catch (\Exception $e) {
            $this->error('Error saving category: ' . $e->getMessage());
        }
    }

    public function deleteCategory(Category $category)
    {
        try {
            if ($category->products()->exists()) {
                $this->error('Cannot delete category with existing products. Move products first or mark as inactive.');
                return;
            }

            $category->delete();
            $this->success('Category deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting category: ' . $e->getMessage());
        }
    }

    public function toggleCategoryStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'activated' : 'deactivated';
        $this->success("Category {$status} successfully!");
    }

    // Subcategory methods
    public function openSubcategoryModal(Category $category)
    {
        $this->resetSubcategoryForm();
        $this->category_id = $category->id;
        $this->selectedCategory = $category;
        $this->editMode = false;
        $this->selectedSubcategory = null;
        $this->showSubcategoryModal = true;
        $this->resetValidation();
    }

    public function editSubcategory(Subcategory $subcategory)
    {
        $this->selectedSubcategory = $subcategory;
        $this->subcategory_name = $subcategory->name;
        $this->subcategory_description = $subcategory->description ?? '';
        $this->subcategory_sort_order = $subcategory->sort_order;
        $this->subcategory_is_active = $subcategory->is_active;
        $this->category_id = $subcategory->category_id;
        $this->selectedCategory = $subcategory->category;
        $this->editMode = true;
        $this->showSubcategoryModal = true;
        $this->resetValidation();
    }

    public function saveSubcategory()
    {
        $this->validate($this->subcategoryRules);

        try {
            $data = [
                'name' => $this->subcategory_name,
                'description' => $this->subcategory_description,
                'sort_order' => $this->subcategory_sort_order ?? 0,
                'is_active' => $this->subcategory_is_active,
                'category_id' => $this->category_id,
            ];

            if ($this->editMode) {
                $this->selectedSubcategory->update($data);
                $this->success('Subcategory updated successfully!');
            } else {
                Subcategory::create($data);
                $this->success('Subcategory created successfully!');
            }

            $this->showSubcategoryModal = false;
            $this->resetSubcategoryForm();
        } catch (\Exception $e) {
            $this->error('Error saving subcategory: ' . $e->getMessage());
        }
    }

    public function deleteSubcategory(Subcategory $subcategory)
    {
        try {
            if ($subcategory->products()->exists()) {
                $this->error('Cannot delete subcategory with existing products. Move products first or mark as inactive.');
                return;
            }

            $subcategory->delete();
            $this->success('Subcategory deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting subcategory: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter']);
    }

    private function resetCategoryForm()
    {
        $this->reset(['name', 'description', 'icon', 'sort_order', 'is_active']);
        $this->is_active = true;
    }

    private function resetSubcategoryForm()
    {
        $this->reset(['subcategory_name', 'subcategory_description', 'subcategory_sort_order', 'subcategory_is_active']);
        $this->subcategory_is_active = true;
    }
}
