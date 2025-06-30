<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">Inventory Management</h2>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Access Level:</strong> Available to Administrators, Managers, and Warehouse Staff
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Products Management</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Inventory > Products</strong> to manage your motorcycle parts inventory.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Adding a New Product</h4>
            <div class="w-full mb-4 steps steps-vertical lg:steps-horizontal">
                <div class="step step-primary">Basic Info</div>
                <div class="step step-primary">Pricing</div>
                <div class="step step-primary">Inventory</div>
                <div class="step">Compatibility</div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <h5 class="mb-2 font-semibold">Required Fields:</h5>
                    <ul class="space-y-1 text-sm text-base-content/80">
                        <li>Product Name</li>
                        <li>SKU (Stock Keeping Unit)</li>
                        <li>Category</li>
                        <li>Cost Price</li>
                        <li>Selling Price</li>
                        <li>Initial Stock Quantity</li>
                    </ul>
                </div>
                <div>
                    <h5 class="mb-2 font-semibold">Optional Fields:</h5>
                    <ul class="space-y-1 text-sm text-base-content/80">
                        <li>Barcode</li>
                        <li>Minimum Stock Level</li>
                        <li>Reorder Point</li>
                        <li>Warranty Period</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Categories & Subcategories</h3>
    <p class="mb-4 text-base-content/80">
        Organize your products using categories. Navigate to <strong>Inventory > Categories</strong>.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Category Examples</th>
                    <th>Subcategory Examples</th>
                    <th>Product Examples</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Engine Parts</td>
                    <td>Pistons, Cylinders, Valves</td>
                    <td>Honda CBR Piston Set</td>
                </tr>
                <tr>
                    <td>Electrical</td>
                    <td>Batteries, Bulbs, Wiring</td>
                    <td>12V Motorcycle Battery</td>
                </tr>
                <tr>
                    <td>Body Parts</td>
                    <td>Fairings, Mirrors, Seats</td>
                    <td>Yamaha R15 Side Mirror</td>
                </tr>
                <tr>
                    <td>Maintenance</td>
                    <td>Oils, Filters, Fluids</td>
                    <td>10W-40 Engine Oil</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Stock Levels & Monitoring</h3>
    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-chart-bar" class="w-5 h-5 text-primary" />
                    Stock Levels
                </h4>
                <p class="mb-3 text-sm text-base-content/80">
                    Monitor current stock across all warehouses and locations.
                </p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>In Stock</span>
                        <span class="badge badge-success">Available</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Low Stock</span>
                        <span class="badge badge-warning">Reorder Soon</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Out of Stock</span>
                        <span class="badge badge-error">Urgent</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-arrow-path" class="w-5 h-5 text-primary" />
                    Stock Movements
                </h4>
                <p class="mb-3 text-sm text-base-content/80">
                    Track all inventory movements and changes.
                </p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Sales</span>
                        <span class="text-error">- Decreases Stock</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Purchases</span>
                        <span class="text-success">+ Increases Stock</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Adjustments</span>
                        <span class="text-warning">± Manual Changes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Stock Adjustments</h3>
    <p class="mb-4 text-base-content/80">
        When you need to manually adjust stock levels for damaged goods, theft, or counting discrepancies.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Adjustment Types</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-plus" class="w-8 h-8 mx-auto mb-2 text-success" />
                    <h5 class="font-semibold">Stock In</h5>
                    <p class="text-sm text-base-content/80">Found items, corrections</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-minus" class="w-8 h-8 mx-auto mb-2 text-error" />
                    <h5 class="font-semibold">Stock Out</h5>
                    <p class="text-sm text-base-content/80">Damaged, lost items</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-clipboard-document-check" class="w-8 h-8 mx-auto mb-2 text-info" />
                    <h5 class="font-semibold">Cycle Count</h5>
                    <p class="text-sm text-base-content/80">Physical count corrections</p>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Warehouses & Locations</h3>
    <p class="mb-4 text-base-content/80">
        Set up multiple warehouses and specific locations within each warehouse for better organization.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Purpose</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Multiple Warehouses</td>
                    <td>Different storage facilities</td>
                    <td>Main Store, Warehouse A, Service Center</td>
                </tr>
                <tr>
                    <td>Inventory Locations</td>
                    <td>Specific spots within warehouse</td>
                    <td>Aisle A-1, Shelf B-3, Counter Display</td>
                </tr>
                <tr>
                    <td>Stock Transfers</td>
                    <td>Move stock between locations</td>
                    <td>Transfer from Warehouse to Store</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Barcode & RFID Support</h3>
    <div class="mb-6 alert alert-success">
        <x-mary-icon name="o-qr-code" class="w-6 h-6" />
        <div>
            <strong>Barcode Scanning:</strong> Use F4 in POS or product management to activate barcode scanner mode.
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Barcode Benefits</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Faster product lookup</li>
                    <li>Reduced data entry errors</li>
                    <li>Quick stock counts</li>
                    <li>Efficient sales processing</li>
                </ul>
            </div>
        </div>
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Scanner Setup</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Connect USB barcode scanner</li>
                    <li>Configure scanner to add Enter after scan</li>
                    <li>Test with existing product barcodes</li>
                    <li>Print barcode labels for new products</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Low Stock Alerts</h3>
    <p class="mb-4 text-base-content/80">
        Set up automated alerts when products reach minimum stock levels.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Alert Configuration</h4>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 rounded bg-base-200">
                    <div>
                        <div class="font-semibold">Minimum Stock Level</div>
                        <div class="text-sm text-base-content/70">Trigger when stock reaches this level</div>
                    </div>
                    <div class="badge badge-warning">Set per product</div>
                </div>
                <div class="flex items-center justify-between p-3 rounded bg-base-200">
                    <div>
                        <div class="font-semibold">Reorder Point</div>
                        <div class="text-sm text-base-content/70">Suggested reorder quantity</div>
                    </div>
                    <div class="badge badge-info">Optional</div>
                </div>
                <div class="flex items-center justify-between p-3 rounded bg-base-200">
                    <div>
                        <div class="font-semibold">Auto Purchase Orders</div>
                        <div class="text-sm text-base-content/70">Generate PO automatically</div>
                    </div>
                    <div class="badge badge-success">Advanced</div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Best Practices</h3>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="card bg-success text-success-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✓ Do This</h4>
                <ul class="space-y-1 text-sm">
                    <li>Use consistent SKU naming</li>
                    <li>Set realistic minimum stock levels</li>
                    <li>Regularly review and adjust prices</li>
                    <li>Keep product information updated</li>
                    <li>Use categories for organization</li>
                </ul>
            </div>
        </div>
        <div class="card bg-error text-error-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✗ Avoid This</h4>
                <ul class="space-y-1 text-sm">
                    <li>Duplicate SKUs</li>
                    <li>Setting minimum stock too low</li>
                    <li>Ignoring low stock alerts</li>
                    <li>Manual adjustments without reasons</li>
                    <li>Poor category organization</li>
                </ul>
            </div>
        </div>
    </div>
</div>
