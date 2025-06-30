<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">System Overview</h2>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Important Notice:</strong> This system is designed for internal inventory tracking only.
            For official receipts, use BIR-printed receipts manually.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">What is this system?</h3>
    <p class="mb-4 text-base-content/80">
        The Motorcycle Parts Inventory Management System is a comprehensive solution designed specifically for
        motorcycle parts businesses.
        It helps you manage your inventory, process sales, track suppliers, and generate detailed reports.
    </p>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Core Features</h3>
    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title text-primary">
                    <x-mary-icon name="o-cube" class="w-5 h-5" />
                    Inventory Management
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Centralized stock management</li>
                    <li>Product categorization</li>
                    <li>Real-time inventory tracking</li>
                    <li>Barcode/RFID support</li>
                    <li>Automated reordering</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title text-primary">
                    <x-mary-icon name="o-shopping-cart" class="w-5 h-5" />
                    Sales & POS
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Touch-friendly POS interface</li>
                    <li>Sales history tracking</li>
                    <li>Customer management</li>
                    <li>Returns & exchanges</li>
                    <li>Warranty tracking</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title text-primary">
                    <x-mary-icon name="o-truck" class="w-5 h-5" />
                    Purchasing
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Purchase order management</li>
                    <li>Supplier tracking</li>
                    <li>Cost tracking</li>
                    <li>Lead time monitoring</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title text-primary">
                    <x-mary-icon name="o-chart-pie" class="w-5 h-5" />
                    Reports & Analytics
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Sales reports</li>
                    <li>Inventory reports</li>
                    <li>Financial analysis</li>
                    <li>Customer insights</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">User Roles</h3>
    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Description</th>
                    <th>Key Permissions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-error">Administrator</span></td>
                    <td>Full system access and management</td>
                    <td>All features, user management, system settings</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">Manager</span></td>
                    <td>Operational management and reporting</td>
                    <td>Inventory, sales, purchasing, reports</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">Cashier</span></td>
                    <td>Sales processing and customer service</td>
                    <td>POS, sales history, customer management</td>
                </tr>
                <tr>
                    <td><span class="badge badge-success">Warehouse Staff</span></td>
                    <td>Inventory and stock management</td>
                    <td>Inventory management, stock adjustments</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Technology Stack</h3>
    <div class="p-4 mb-6 rounded-lg bg-base-100">
        <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div class="text-center">
                <div class="font-semibold text-primary">Frontend</div>
                <div class="text-base-content/80">Tailwind CSS, Alpine.js</div>
            </div>
            <div class="text-center">
                <div class="font-semibold text-primary">Backend</div>
                <div class="text-base-content/80">Laravel, Livewire</div>
            </div>
            <div class="text-center">
                <div class="font-semibold text-primary">UI Components</div>
                <div class="text-base-content/80">MaryUI v2</div>
            </div>
            <div class="text-center">
                <div class="font-semibold text-primary">Database</div>
                <div class="text-base-content/80">MySQL</div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Legal Compliance</h3>
    <div class="mb-6 alert alert-warning">
        <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
        <div>
            <strong>BIR Compliance:</strong> This system generates sales drafts and provisional receipts for internal
            tracking.
            For official receipts required by BIR, issue manually printed official receipts.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Getting Help</h3>
    <p class="mb-4 text-base-content/80">
        Use the search function at the top of this manual to quickly find specific topics.
        Each section contains detailed step-by-step instructions with screenshots and examples.
    </p>
</div>
