<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">Getting Started</h2>

    <h3 class="mb-4 text-xl font-semibold text-base-content">First Time Login</h3>
    <div class="w-full mb-6 steps">
        <div class="step step-primary">Login</div>
        <div class="step step-primary">Setup Profile</div>
        <div class="step">Configure System</div>
        <div class="step">Add Products</div>
        <div class="step">Start Selling</div>
    </div>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Default Admin Credentials:</strong> Contact your system administrator for login credentials.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Dashboard Overview</h3>
    <p class="mb-4 text-base-content/80">
        After logging in, you'll see the main dashboard which provides an overview of your business metrics:
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-chart-bar" class="w-5 h-5 text-primary" />
                    Key Metrics
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Today's Sales</li>
                    <li>Monthly Revenue</li>
                    <li>Low Stock Alerts</li>
                    <li>Pending Orders</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-bolt" class="w-5 h-5 text-primary" />
                    Quick Actions
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>New Sale</li>
                    <li>Add Product</li>
                    <li>Stock Adjustment</li>
                    <li>New Purchase Order</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Navigation Menu</h3>
    <p class="mb-4 text-base-content/80">
        The left sidebar contains all system modules organized by function:
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Purpose</th>
                    <th>Available To</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-cube" class="w-4 h-4" />
                            Inventory
                        </div>
                    </td>
                    <td>Manage products, categories, stock levels</td>
                    <td>Admin, Manager, Warehouse Staff</td>
                </tr>
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-shopping-cart" class="w-4 h-4" />
                            Sales
                        </div>
                    </td>
                    <td>Process sales, manage customers, returns</td>
                    <td>Admin, Manager, Cashier</td>
                </tr>
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-truck" class="w-4 h-4" />
                            Purchasing
                        </div>
                    </td>
                    <td>Purchase orders, supplier management</td>
                    <td>Admin, Manager, Warehouse Staff</td>
                </tr>
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-chart-pie" class="w-4 h-4" />
                            Reports
                        </div>
                    </td>
                    <td>Sales, inventory, financial reports</td>
                    <td>Admin, Manager</td>
                </tr>
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-cog-6-tooth" class="w-4 h-4" />
                            Administration
                        </div>
                    </td>
                    <td>User management, system settings</td>
                    <td>Admin only</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Initial Setup Checklist</h3>
    <div class="p-6 mb-6 rounded-lg bg-base-100">
        <h4 class="mb-4 font-semibold text-base-content">Before you start selling:</h4>
        <div class="space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-primary" />
                <span class="text-base-content/80">Set up warehouses and inventory locations</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-primary" />
                <span class="text-base-content/80">Create product categories and subcategories</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-primary" />
                <span class="text-base-content/80">Add your first products with proper SKUs</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-primary" />
                <span class="text-base-content/80">Set up suppliers and their contact information</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-primary" />
                <span class="text-base-content/80">Configure user accounts and permissions</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-primary" />
                <span class="text-base-content/80">Set minimum stock levels for automatic reordering</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-primary" />
                <span class="text-base-content/80">Test the POS system with a sample sale</span>
            </label>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Basic Workflow</h3>
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <div class="card bg-primary text-primary-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-plus" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">1. Add Products</h4>
                <p class="text-sm opacity-90">Create products with SKUs, prices, and stock levels</p>
            </div>
        </div>
        <div class="card bg-secondary text-secondary-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-shopping-cart" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">2. Process Sales</h4>
                <p class="text-sm opacity-90">Use POS to sell products and track inventory</p>
            </div>
        </div>
        <div class="card bg-accent text-accent-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-chart-bar" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">3. Monitor & Reorder</h4>
                <p class="text-sm opacity-90">Track stock levels and reorder when needed</p>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Keyboard Shortcuts</h3>
    <div class="mb-6 overflow-x-auto">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Shortcut</th>
                    <th>Context</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Quick Search</td>
                    <td><kbd class="kbd kbd-sm">Ctrl</kbd> + <kbd class="kbd kbd-sm">K</kbd></td>
                    <td>Global</td>
                </tr>
                <tr>
                    <td>New Sale</td>
                    <td><kbd class="kbd kbd-sm">F1</kbd></td>
                    <td>Dashboard</td>
                </tr>
                <tr>
                    <td>Add Product to Sale</td>
                    <td><kbd class="kbd kbd-sm">F2</kbd></td>
                    <td>POS</td>
                </tr>
                <tr>
                    <td>Complete Sale</td>
                    <td><kbd class="kbd kbd-sm">F3</kbd></td>
                    <td>POS</td>
                </tr>
                <tr>
                    <td>Barcode Scanner</td>
                    <td><kbd class="kbd kbd-sm">F4</kbd></td>
                    <td>POS</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="alert alert-success">
        <x-mary-icon name="o-check-circle" class="w-6 h-6" />
        <div>
            <strong>Ready to go!</strong> Once you complete the initial setup, you'll be ready to start managing your
            motorcycle parts inventory efficiently.
        </div>
    </div>
</div>
