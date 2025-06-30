<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">Sales & Point of Sale (POS)</h2>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Access Level:</strong> Available to Administrators, Managers, and Cashiers
        </div>
    </div>

    <div class="mb-6 alert alert-warning">
        <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
        <div>
            <strong>Legal Notice:</strong> This system generates sales drafts for internal tracking. Issue BIR-printed
            official receipts manually for legal compliance.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Point of Sale (POS) Interface</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Sales > Point of Sale</strong> to access the main selling interface.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-6 card-body">
                <h4 class="mb-4 text-lg card-title">
                    <x-mary-icon name="o-shopping-cart" class="w-5 h-5 text-primary" />
                    POS Layout
                </h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-2 rounded bg-base-200">
                        <span class="text-sm">Product Search</span>
                        <span class="badge badge-primary">Top</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-base-200">
                        <span class="text-sm">Cart Items</span>
                        <span class="badge badge-secondary">Left</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-base-200">
                        <span class="text-sm">Payment Options</span>
                        <span class="badge badge-accent">Right</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-base-200">
                        <span class="text-sm">Total & Actions</span>
                        <span class="badge badge-info">Bottom</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Processing a Sale</h3>
    <div class="w-full mb-6 steps">
        <div class="step step-primary">Add Products</div>
        <div class="step step-primary">Apply Discounts</div>
        <div class="step step-primary">Select Payment</div>
        <div class="step step-primary">Complete Sale</div>
        <div class="step">Print Receipt</div>
    </div>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Step-by-Step Process</h4>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="sale-process" />
                <div class="text-lg font-medium collapse-title">
                    1. Adding Products to Cart
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Method 1:</strong> Type product name or SKU in search box</p>
                        <p><strong>Method 2:</strong> Scan barcode using <kbd class="kbd kbd-xs">F1</kbd> to open
                            scanner</p>
                        <p><strong>Method 3:</strong> Browse categories and click products</p>
                        <p><strong>Keyboard Shortcut:</strong> Press <kbd class="kbd kbd-xs">F1</kbd> to open barcode
                            scanner modal</p>
                        <p><strong>Note:</strong> Quantity can be adjusted after adding to cart</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="sale-process" />
                <div class="text-lg font-medium collapse-title">
                    2. Applying Discounts
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Item Discount:</strong> Apply to individual products</p>
                        <p><strong>Total Discount:</strong> Apply to entire cart</p>
                        <p><strong>Percentage:</strong> Use % symbol (e.g., 10%)</p>
                        <p><strong>Fixed Amount:</strong> Enter peso amount (e.g., 50)</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="sale-process" />
                <div class="text-lg font-medium collapse-title">
                    3. Payment Methods
                </div>
                <div class="collapse-content">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p><strong>Cash Payment</strong></p>
                            <ul class="mt-1 list-disc list-inside">
                                <li>Enter amount received</li>
                                <li>System calculates change</li>
                                <li>Print receipt</li>
                            </ul>
                        </div>
                        <div>
                            <p><strong>Card/Digital Payment</strong></p>
                            <ul class="mt-1 list-disc list-inside">
                                <li>Select payment type</li>
                                <li>Enter reference number</li>
                                <li>No change calculation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-200">
                <input type="radio" name="sale-process" />
                <div class="text-lg font-medium collapse-title">
                    4. Completing the Sale
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Review:</strong> Check all items, quantities, and total</p>
                        <p><strong>Customer Info:</strong> Optional - add customer details</p>
                        <p><strong>Complete:</strong> Click "Complete Sale" or press <kbd class="kbd kbd-xs">F2</kbd>
                        </p>
                        <p><strong>Receipt:</strong> System generates sales draft</p>
                        <p><strong>Keyboard Shortcut:</strong> Press <kbd class="kbd kbd-xs">F2</kbd> to open payment
                            modal</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">POS Keyboard Shortcuts</h3>
    <p class="mb-4 text-base-content/80">
        The Point of Sale system includes keyboard shortcuts for faster operation and improved cashier efficiency.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Available Keyboard Shortcuts</h4>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Shortcut Key</th>
                            <th>Function</th>
                            <th>Description</th>
                            <th>When Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-primary/5">
                            <td><kbd class="kbd">F1</kbd></td>
                            <td><strong>Open Barcode Scanner</strong></td>
                            <td>Opens the barcode scanner modal for quick product scanning</td>
                            <td>Always available</td>
                        </tr>
                        <tr class="bg-secondary/5">
                            <td><kbd class="kbd">F2</kbd></td>
                            <td><strong>Complete Sale</strong></td>
                            <td>Opens payment modal to process payment and complete sale</td>
                            <td>When cart has items</td>
                        </tr>
                        <tr class="bg-warning/5">
                            <td><kbd class="kbd">F3</kbd></td>
                            <td><strong>Clear Cart</strong></td>
                            <td>Clears all items from the current cart (with confirmation)</td>
                            <td>When cart has items</td>
                        </tr>
                        <tr class="bg-info/5">
                            <td><kbd class="kbd">F4</kbd></td>
                            <td><strong>Hold Sale</strong></td>
                            <td>Saves current cart for later retrieval</td>
                            <td>When cart has items</td>
                        </tr>
                        <tr class="bg-success/5">
                            <td><kbd class="kbd">F5</kbd></td>
                            <td><strong>View Held Sales</strong></td>
                            <td>Opens modal to view and restore previously held sales</td>
                            <td>Always available</td>
                        </tr>
                        <tr class="bg-neutral/5">
                            <td><kbd class="kbd">Esc</kbd></td>
                            <td><strong>Close Modals</strong></td>
                            <td>Closes any open modal windows or dialogs</td>
                            <td>When modal is open</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-bolt" class="w-5 h-5 text-warning" />
                    Quick Workflow Tips
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>• Use <kbd class="kbd kbd-xs">F1</kbd> to quickly scan barcodes</li>
                    <li>• Press <kbd class="kbd kbd-xs">F2</kbd> when ready to take payment</li>
                    <li>• Hold sales with <kbd class="kbd kbd-xs">F4</kbd> for busy periods</li>
                    <li>• Access held sales anytime with <kbd class="kbd kbd-xs">F5</kbd></li>
                    <li>• Clear mistakes quickly with <kbd class="kbd kbd-xs">F3</kbd></li>
                    <li>• Close dialogs instantly with <kbd class="kbd kbd-xs">Esc</kbd></li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-academic-cap" class="w-5 h-5 text-info" />
                    Best Practices
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>• Learn shortcuts gradually - start with <kbd class="kbd kbd-xs">F1</kbd> and <kbd
                            class="kbd kbd-xs">F2</kbd></li>
                    <li>• Use barcode scanner for speed and accuracy</li>
                    <li>• Hold sales during rush hours to serve multiple customers</li>
                    <li>• Always confirm before clearing cart</li>
                    <li>• Use Esc to cancel unwanted actions</li>
                    <li>• Practice shortcuts during quiet periods</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mb-6 alert alert-success">
        <x-mary-icon name="o-light-bulb" class="w-6 h-6" />
        <div>
            <strong>Pro Tip:</strong> The POS system shows visual shortcuts on buttons. Look for small key indicators in
            the corners of buttons to remember which shortcut does what!
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Hold Sales Feature</h3>
    <p class="mb-4 text-base-content/80">
        The Hold Sales feature allows you to temporarily save a cart and serve other customers, perfect for busy
        periods.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">How to Use Hold Sales</h4>

            <div class="w-full mb-4 steps">
                <div class="step step-primary">Add Items</div>
                <div class="step step-primary">Press F4</div>
                <div class="step step-primary">Serve Next Customer</div>
                <div class="step step-primary">Press F5</div>
                <div class="step">Restore Sale</div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h5 class="mb-2 font-semibold text-success">When to Hold Sales:</h5>
                    <ul class="space-y-1 text-sm">
                        <li>• Customer needs to get more items</li>
                        <li>• Waiting for price check</li>
                        <li>• Customer forgot payment method</li>
                        <li>• Need to serve urgent customer</li>
                        <li>• Complex transaction in progress</li>
                    </ul>
                </div>
                <div>
                    <h5 class="mb-2 font-semibold text-info">Hold Sales Benefits:</h5>
                    <ul class="space-y-1 text-sm">
                        <li>• Serve multiple customers efficiently</li>
                        <li>• No lost sales during interruptions</li>
                        <li>• Maintain customer service quality</li>
                        <li>• Handle complex orders better</li>
                        <li>• Reduce customer wait times</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Sales > Customers</strong> to manage customer information and purchase history.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-user-plus" class="w-5 h-5 text-primary" />
                    Adding Customers
                </h4>
                <div class="space-y-2 text-sm">
                    <div><strong>Required:</strong> Name, Phone</div>
                    <div><strong>Optional:</strong> Email, Address</div>
                    <div><strong>Type:</strong> Individual or Business</div>
                    <div><strong>Groups:</strong> VIP, Regular, etc.</div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-chart-bar" class="w-5 h-5 text-primary" />
                    Customer Benefits
                </h4>
                <div class="space-y-2 text-sm">
                    <div>• Purchase history tracking</div>
                    <div>• Warranty information</div>
                    <div>• Special pricing tiers</div>
                    <div>• Return/exchange tracking</div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Sales History & Reports</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Sales > Sales History</strong> to view and manage completed sales.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Description</th>
                    <th>Actions Available</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sales List</td>
                    <td>All completed transactions</td>
                    <td>View, Print, Return</td>
                </tr>
                <tr>
                    <td>Filters</td>
                    <td>Date range, customer, cashier</td>
                    <td>Search, Export</td>
                </tr>
                <tr>
                    <td>Receipt Reprint</td>
                    <td>Reprint sales drafts</td>
                    <td>Download, Print</td>
                </tr>
                <tr>
                    <td>Sale Details</td>
                    <td>Item breakdown, payments</td>
                    <td>View, Modify</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Returns & Exchanges</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Sales > Returns & Exchanges</strong> to process product returns.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Return Process</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-magnifying-glass" class="w-8 h-8 mx-auto mb-2 text-primary" />
                    <h5 class="font-semibold">1. Find Sale</h5>
                    <p class="text-sm text-base-content/80">Search by receipt number or customer</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-arrow-uturn-left" class="w-8 h-8 mx-auto mb-2 text-warning" />
                    <h5 class="font-semibold">2. Select Items</h5>
                    <p class="text-sm text-base-content/80">Choose products to return</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-check-circle" class="w-8 h-8 mx-auto mb-2 text-success" />
                    <h5 class="font-semibold">3. Process Return</h5>
                    <p class="text-sm text-base-content/80">Complete return or exchange</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Return Reasons</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>• Defective product</li>
                    <li>• Wrong item ordered</li>
                    <li>• Customer changed mind</li>
                    <li>• Warranty claim</li>
                    <li>• Damaged in transit</li>
                </ul>
            </div>
        </div>
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Return Actions</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>• Return to stock</li>
                    <li>• Mark as damaged</li>
                    <li>• Exchange for different item</li>
                    <li>• Refund customer</li>
                    <li>• Supplier return</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Warranty Tracking</h3>
    <p class="mb-4 text-base-content/80">
        Track warranty periods and handle warranty claims for sold products.
    </p>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-shield-check" class="w-6 h-6" />
        <div>
            <strong>Automatic Tracking:</strong> Warranty periods are automatically calculated from sale date based on
            product settings.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Shift Management</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Sales > Shift Management</strong> to manage cashier shifts and daily sales tracking.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Purpose</th>
                    <th>Who Can Access</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Open Shift</td>
                    <td>Start daily sales tracking</td>
                    <td>Cashier, Manager, Admin</td>
                </tr>
                <tr>
                    <td>Close Shift</td>
                    <td>End shift with cash count</td>
                    <td>Cashier, Manager, Admin</td>
                </tr>
                <tr>
                    <td>Shift Reports</td>
                    <td>Sales summary for shift</td>
                    <td>Manager, Admin</td>
                </tr>
                <tr>
                    <td>Cash Drawer</td>
                    <td>Track opening/closing cash</td>
                    <td>All sales users</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Receipt Management</h3>
    <div class="mb-6 alert alert-warning">
        <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
        <div>
            <strong>Important:</strong> System receipts are labeled as "Sales Invoice Draft" or "Provisional Receipt".
            Issue BIR-compliant official receipts manually for legal requirements.
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">System Receipt Features</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>• Detailed item breakdown</li>
                    <li>• Payment information</li>
                    <li>• Customer details</li>
                    <li>• Cashier and timestamp</li>
                    <li>• Clear "Draft" labeling</li>
                </ul>
            </div>
        </div>
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Receipt Actions</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>• Print immediately after sale</li>
                    <li>• Reprint from sales history</li>
                    <li>• Email to customer</li>
                    <li>• Export to PDF</li>
                    <li>• Include in reports</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Best Practices</h3>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="card bg-success text-success-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✓ Do This</h4>
                <ul class="space-y-1 text-sm">
                    <li>• Always verify product and quantity</li>
                    <li>• Double-check payment amounts</li>
                    <li>• Issue BIR receipts manually</li>
                    <li>• Record customer information when possible</li>
                    <li>• Process returns promptly</li>
                    <li>• Open and close shifts properly</li>
                </ul>
            </div>
        </div>
        <div class="card bg-error text-error-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✗ Avoid This</h4>
                <ul class="space-y-1 text-sm">
                    <li>• Using system receipts as official receipts</li>
                    <li>• Processing sales without inventory</li>
                    <li>• Ignoring return policies</li>
                    <li>• Leaving shifts open overnight</li>
                    <li>• Manual price overrides without reason</li>
                    <li>• Processing returns without verification</li>
                </ul>
            </div>
        </div>
    </div>
</div>
