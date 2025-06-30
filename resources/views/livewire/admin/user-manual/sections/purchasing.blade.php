<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">Purchasing Management</h2>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Access Level:</strong> Available to Administrators, Managers, and Warehouse Staff
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Supplier Management</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Purchasing > Suppliers</strong> to manage your supplier database.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-building-office-2" class="w-5 h-5 text-primary" />
                    Supplier Information
                </h4>
                <div class="space-y-2 text-sm">
                    <div><strong>Required:</strong> Company Name, Contact Person</div>
                    <div><strong>Contact:</strong> Phone, Email, Address</div>
                    <div><strong>Terms:</strong> Payment terms, lead times</div>
                    <div><strong>Products:</strong> What they supply</div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-presentation-chart-line" class="w-5 h-5 text-primary" />
                    Supplier Tracking
                </h4>
                <div class="space-y-2 text-sm">
                    <div>Purchase history</div>
                    <div>Performance metrics</div>
                    <div>Lead time accuracy</div>
                    <div>Quality ratings</div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Purchase Orders</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Purchasing > Purchase Orders</strong> to create and manage purchase orders.
    </p>

    <div class="w-full mb-6 steps">
        <div class="step step-primary">Create PO</div>
        <div class="step step-primary">Add Products</div>
        <div class="step step-primary">Send to Supplier</div>
        <div class="step step-primary">Receive Goods</div>
        <div class="step">Complete PO</div>
    </div>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Creating a Purchase Order</h4>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="po-process" />
                <div class="text-lg font-medium collapse-title">
                    1. PO Header Information
                </div>
                <div class="collapse-content">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p><strong>Required Fields:</strong></p>
                            <ul class="mt-1 list-disc list-inside">
                                <li>Supplier selection</li>
                                <li>Expected delivery date</li>
                                <li>Warehouse destination</li>
                            </ul>
                        </div>
                        <div>
                            <p><strong>Optional Fields:</strong></p>
                            <ul class="mt-1 list-disc list-inside">
                                <li>Reference number</li>
                                <li>Notes/Instructions</li>
                                <li>Priority level</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="po-process" />
                <div class="text-lg font-medium collapse-title">
                    2. Adding Products
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Product Selection:</strong> Search and add products from your catalog</p>
                        <p><strong>Quantities:</strong> Enter required quantities for each product</p>
                        <p><strong>Cost Prices:</strong> Update supplier cost prices if different</p>
                        <p><strong>Notes:</strong> Add specific requirements per product</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="po-process" />
                <div class="text-lg font-medium collapse-title">
                    3. Review and Send
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Totals Review:</strong> Check quantities and total amount</p>
                        <p><strong>Supplier Details:</strong> Verify delivery address and contact</p>
                        <p><strong>Generate PO:</strong> Create PDF document</p>
                        <p><strong>Send:</strong> Email or print for supplier</p>
                    </div>
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-200">
                <input type="radio" name="po-process" />
                <div class="text-lg font-medium collapse-title">
                    4. Receiving Process
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Partial Receiving:</strong> Mark items as received when delivered</p>
                        <p><strong>Quality Check:</strong> Inspect received goods</p>
                        <p><strong>Stock Update:</strong> System updates inventory automatically</p>
                        <p><strong>Completion:</strong> Close PO when fully received</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Purchase Order Status</h3>
    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Actions Available</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-info">Draft</span></td>
                    <td>PO created but not sent</td>
                    <td>Edit, Delete, Send</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td>Sent to supplier, awaiting delivery</td>
                    <td>View, Receive, Cancel</td>
                </tr>
                <tr>
                    <td><span class="badge badge-primary">Partial</span></td>
                    <td>Some items received</td>
                    <td>Continue receiving, View</td>
                </tr>
                <tr>
                    <td><span class="badge badge-success">Completed</span></td>
                    <td>All items received</td>
                    <td>View, Print, Archive</td>
                </tr>
                <tr>
                    <td><span class="badge badge-error">Cancelled</span></td>
                    <td>PO cancelled before completion</td>
                    <td>View only</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Automated Reordering</h3>
    <p class="mb-4 text-base-content/80">
        Set up automatic purchase order generation when products reach minimum stock levels.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Reorder Configuration</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-bell" class="w-8 h-8 mx-auto mb-2 text-warning" />
                    <h5 class="font-semibold">1. Set Triggers</h5>
                    <p class="text-sm text-base-content/80">Configure minimum stock levels per product</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-cog-6-tooth" class="w-8 h-8 mx-auto mb-2 text-info" />
                    <h5 class="font-semibold">2. Auto Generate</h5>
                    <p class="text-sm text-base-content/80">System creates PO automatically</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-check-circle" class="w-8 h-8 mx-auto mb-2 text-success" />
                    <h5 class="font-semibold">3. Review & Send</h5>
                    <p class="text-sm text-base-content/80">Manager reviews before sending</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Reorder Settings</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-2 rounded bg-base-200">
                        <span class="text-sm">Minimum Stock Level</span>
                        <span class="badge badge-warning">Per Product</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-base-200">
                        <span class="text-sm">Reorder Quantity</span>
                        <span class="badge badge-info">Suggested Amount</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-base-200">
                        <span class="text-sm">Lead Time</span>
                        <span class="badge badge-primary">Days to Delivery</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Benefits</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Prevent stockouts</li>
                    <li>Reduce manual monitoring</li>
                    <li>Optimize inventory levels</li>
                    <li>Maintain supplier relationships</li>
                    <li>Improve cash flow</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Cost Tracking & Analysis</h3>
    <p class="mb-4 text-base-content/80">
        Monitor and analyze purchasing costs to optimize your procurement process.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Description</th>
                    <th>Where to Find</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Purchase Volume</td>
                    <td>Total purchase amounts by period</td>
                    <td>Reports > Financial</td>
                </tr>
                <tr>
                    <td>Supplier Performance</td>
                    <td>On-time delivery, quality metrics</td>
                    <td>Purchasing > Suppliers</td>
                </tr>
                <tr>
                    <td>Cost Trends</td>
                    <td>Price changes over time</td>
                    <td>Reports > Inventory</td>
                </tr>
                <tr>
                    <td>Lead Time Analysis</td>
                    <td>Average delivery times</td>
                    <td>Purchasing > Purchase Orders</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Receiving Process</h3>
    <p class="mb-4 text-base-content/80">
        Proper receiving ensures accurate inventory updates and quality control.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Receiving Workflow</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="p-3 text-center border rounded border-base-300">
                    <x-mary-icon name="o-truck" class="w-6 h-6 mx-auto mb-2 text-primary" />
                    <div class="text-sm font-semibold">Delivery Arrives</div>
                </div>
                <div class="p-3 text-center border rounded border-base-300">
                    <x-mary-icon name="o-clipboard-document-check" class="w-6 h-6 mx-auto mb-2 text-info" />
                    <div class="text-sm font-semibold">Check Against PO</div>
                </div>
                <div class="p-3 text-center border rounded border-base-300">
                    <x-mary-icon name="o-eye" class="w-6 h-6 mx-auto mb-2 text-warning" />
                    <div class="text-sm font-semibold">Quality Inspection</div>
                </div>
                <div class="p-3 text-center border rounded border-base-300">
                    <x-mary-icon name="o-check-circle" class="w-6 h-6 mx-auto mb-2 text-success" />
                    <div class="text-sm font-semibold">Update Inventory</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Receiving Options</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Full Receiving</span>
                        <span class="badge badge-success">All items</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Partial Receiving</span>
                        <span class="badge badge-warning">Some items</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Over Delivery</span>
                        <span class="badge badge-info">More than ordered</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Damaged Goods</span>
                        <span class="badge badge-error">Quality issues</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Quality Control</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Check for physical damage</li>
                    <li>Verify part numbers match</li>
                    <li>Test functionality if applicable</li>
                    <li>Document any discrepancies</li>
                    <li>Photo evidence for issues</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Vendor Performance Tracking</h3>
    <p class="mb-4 text-base-content/80">
        Monitor supplier performance to make informed purchasing decisions.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Performance Metric</th>
                    <th>How It's Calculated</th>
                    <th>Good Performance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>On-Time Delivery</td>
                    <td>Orders delivered by promised date</td>
                    <td>>95%</td>
                </tr>
                <tr>
                    <td>Order Accuracy</td>
                    <td>Correct items and quantities</td>
                    <td>>98%</td>
                </tr>
                <tr>
                    <td>Quality Rating</td>
                    <td>Items received without defects</td>
                    <td>>99%</td>
                </tr>
                <tr>
                    <td>Lead Time Consistency</td>
                    <td>Actual vs. promised delivery time</td>
                    <td>±1 day</td>
                </tr>
                <tr>
                    <td>Price Competitiveness</td>
                    <td>Pricing compared to market</td>
                    <td>Market rate or better</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Purchase Order Templates</h3>
    <p class="mb-4 text-base-content/80">
        Create templates for frequently ordered products to speed up the purchasing process.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Template Benefits</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 text-center rounded bg-primary/10">
                    <x-mary-icon name="o-clock" class="w-8 h-8 mx-auto mb-2 text-primary" />
                    <h5 class="font-semibold">Time Saving</h5>
                    <p class="text-sm text-base-content/80">Quick PO creation for regular orders</p>
                </div>
                <div class="p-4 text-center rounded bg-secondary/10">
                    <x-mary-icon name="o-check-circle" class="w-8 h-8 mx-auto mb-2 text-secondary" />
                    <h5 class="font-semibold">Consistency</h5>
                    <p class="text-sm text-base-content/80">Standard quantities and terms</p>
                </div>
                <div class="p-4 text-center rounded bg-accent/10">
                    <x-mary-icon name="o-arrow-trending-down" class="w-8 h-8 mx-auto mb-2 text-accent" />
                    <h5 class="font-semibold">Error Reduction</h5>
                    <p class="text-sm text-base-content/80">Pre-validated product lists</p>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Integration with Inventory</h3>
    <p class="mb-4 text-base-content/80">
        Purchasing integrates seamlessly with inventory management for automatic updates.
    </p>

    <div class="mb-6 alert alert-success">
        <x-mary-icon name="o-arrow-path" class="w-6 h-6" />
        <div>
            <strong>Automatic Updates:</strong> When goods are received, inventory levels update automatically and stock
            movements are recorded.
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">What Updates Automatically</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Stock quantities increase</li>
                    <li>Cost prices update if changed</li>
                    <li>Stock movements recorded</li>
                    <li>Warehouse locations assigned</li>
                    <li>Supplier cost history</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Manual Adjustments</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Damaged goods handling</li>
                    <li>Over-delivery processing</li>
                    <li>Quality control rejections</li>
                    <li>Location assignments</li>
                    <li>Cost price corrections</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Export and Import</h3>
    <p class="mb-4 text-base-content/80">
        Export purchase orders to Excel or import supplier catalogs for easier management.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>Export/Import Type</th>
                    <th>Purpose</th>
                    <th>Format</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Purchase Order Export</td>
                    <td>Send formatted PO to suppliers</td>
                    <td>PDF, Excel</td>
                </tr>
                <tr>
                    <td>Supplier Catalog Import</td>
                    <td>Bulk update product costs</td>
                    <td>CSV, Excel</td>
                </tr>
                <tr>
                    <td>Purchase History Export</td>
                    <td>Analysis and record keeping</td>
                    <td>Excel, CSV</td>
                </tr>
                <tr>
                    <td>Receiving Report</td>
                    <td>Goods received documentation</td>
                    <td>PDF</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Best Practices</h3>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="card bg-success text-success-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✓ Do This</h4>
                <ul class="space-y-1 text-sm">
                    <li>Maintain accurate supplier information</li>
                    <li>Set realistic lead times</li>
                    <li>Negotiate payment terms</li>
                    <li>Monitor supplier performance</li>
                    <li>Use templates for regular orders</li>
                    <li>Document receiving discrepancies</li>
                    <li>Review and approve POs before sending</li>
                </ul>
            </div>
        </div>
        <div class="card bg-error text-error-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✗ Avoid This</h4>
                <ul class="space-y-1 text-sm">
                    <li>Creating POs without checking inventory</li>
                    <li>Ignoring supplier performance issues</li>
                    <li>Accepting damaged goods without documentation</li>
                    <li>Manual inventory updates</li>
                    <li>Ordering without considering lead times</li>
                    <li>Poor communication with suppliers</li>
                    <li>Overstocking slow-moving items</li>
                </ul>
            </div>
        </div>
    </div>
</div>
