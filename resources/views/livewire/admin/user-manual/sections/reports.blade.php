<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">Reports & Analytics</h2>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Access Level:</strong> Available to Administrators and Managers. Limited access for Cashiers.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Reports Overview</h3>
    <p class="mb-4 text-base-content/80">
        The reporting system provides comprehensive insights into your business performance across sales, inventory,
        finances, and customers.
    </p>

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="card bg-primary text-primary-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-chart-bar" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Sales Reports</h4>
                <p class="text-sm opacity-90">Revenue, transactions, performance</p>
            </div>
        </div>
        <div class="card bg-secondary text-secondary-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-cube" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Inventory Reports</h4>
                <p class="text-sm opacity-90">Stock levels, movements, valuation</p>
            </div>
        </div>
        <div class="card bg-accent text-accent-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-banknotes" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Financial Reports</h4>
                <p class="text-sm opacity-90">Profit, costs, cash flow</p>
            </div>
        </div>
        <div class="card bg-info text-info-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-users" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Customer Reports</h4>
                <p class="text-sm opacity-90">Behavior, loyalty, demographics</p>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Sales Reports</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Reports > Sales Reports</strong> to analyze your sales performance.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Available Sales Reports</h4>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Report Type</th>
                            <th>Description</th>
                            <th>Key Metrics</th>
                            <th>Export Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Daily Sales Summary</td>
                            <td>Sales performance by day</td>
                            <td>Revenue, transactions, items sold</td>
                            <td>PDF, Excel</td>
                        </tr>
                        <tr>
                            <td>Product Performance</td>
                            <td>Top/bottom selling products</td>
                            <td>Quantity sold, revenue, profit</td>
                            <td>PDF, Excel</td>
                        </tr>
                        <tr>
                            <td>Sales by Category</td>
                            <td>Performance by product category</td>
                            <td>Category revenue, growth trends</td>
                            <td>PDF, Excel</td>
                        </tr>
                        <tr>
                            <td>Cashier Performance</td>
                            <td>Sales by staff member</td>
                            <td>Transactions, revenue per cashier</td>
                            <td>PDF, Excel</td>
                        </tr>
                        <tr>
                            <td>Payment Methods</td>
                            <td>Breakdown by payment type</td>
                            <td>Cash vs. card percentages</td>
                            <td>PDF, Excel</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Report Filters</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Date range selection</li>
                    <li>Product categories</li>
                    <li>Specific products</li>
                    <li>Customer segments</li>
                    <li>Cashier/user</li>
                    <li>Payment methods</li>
                    <li>Warehouse/location</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Visual Charts</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Revenue trend lines</li>
                    <li>Category pie charts</li>
                    <li>Product performance bars</li>
                    <li>Monthly comparisons</li>
                    <li>Growth rate graphs</li>
                    <li>Payment method distribution</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Inventory Reports</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Reports > Inventory Reports</strong> to monitor stock levels and movements.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Inventory Analysis</h4>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-chart-bar" class="w-8 h-8 mx-auto mb-2 text-primary" />
                    <h5 class="font-semibold">Stock Levels</h5>
                    <p class="text-sm text-base-content/80">Current quantities by product and location</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-arrow-path" class="w-8 h-8 mx-auto mb-2 text-secondary" />
                    <h5 class="font-semibold">Stock Movements</h5>
                    <p class="text-sm text-base-content/80">All inventory transactions and changes</p>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-currency-dollar" class="w-8 h-8 mx-auto mb-2 text-accent" />
                    <h5 class="font-semibold">Inventory Valuation</h5>
                    <p class="text-sm text-base-content/80">Total value of stock on hand</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>Report</th>
                    <th>Purpose</th>
                    <th>Frequency</th>
                    <th>Key Users</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Low Stock Alert</td>
                    <td>Products below minimum levels</td>
                    <td>Daily</td>
                    <td>Warehouse Staff, Managers</td>
                </tr>
                <tr>
                    <td>Fast/Slow Moving Items</td>
                    <td>Inventory turnover analysis</td>
                    <td>Monthly</td>
                    <td>Buyers, Managers</td>
                </tr>
                <tr>
                    <td>Dead Stock Report</td>
                    <td>Items with no sales activity</td>
                    <td>Quarterly</td>
                    <td>Management</td>
                </tr>
                <tr>
                    <td>Stock Accuracy</td>
                    <td>Physical vs. system counts</td>
                    <td>After cycle counts</td>
                    <td>Warehouse, Management</td>
                </tr>
                <tr>
                    <td>Reorder Suggestions</td>
                    <td>Recommended purchase quantities</td>
                    <td>Weekly</td>
                    <td>Purchasing Team</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Financial Reports</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Reports > Financial Reports</strong> to analyze profitability and costs.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Profit Analysis</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Gross Profit</span>
                        <span class="text-success">Revenue - COGS</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Gross Margin %</span>
                        <span class="text-info">Profit / Revenue</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Product Profitability</span>
                        <span class="text-primary">By item/category</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Trend Analysis</span>
                        <span class="text-secondary">Month-over-month</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Cost Tracking</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Cost of goods sold (COGS)</li>
                    <li>Purchase cost trends</li>
                    <li>Supplier cost comparison</li>
                    <li>Inventory carrying costs</li>
                    <li>Markup analysis</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Customer Reports</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Reports > Customer Reports</strong> to understand customer behavior and loyalty.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Customer Analytics</h4>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="p-3 text-center rounded bg-primary/10">
                    <div class="text-2xl stat-value text-primary">Top</div>
                    <div class="stat-title">Customers by Revenue</div>
                </div>
                <div class="p-3 text-center rounded bg-secondary/10">
                    <div class="text-2xl stat-value text-secondary">New</div>
                    <div class="stat-title">Customer Acquisition</div>
                </div>
                <div class="p-3 text-center rounded bg-accent/10">
                    <div class="text-2xl stat-value text-accent">RFM</div>
                    <div class="stat-title">Recency, Frequency, Monetary</div>
                </div>
                <div class="p-3 text-center rounded bg-info/10">
                    <div class="text-2xl stat-value text-info">CLV</div>
                    <div class="stat-title">Customer Lifetime Value</div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Report Generation & Export</h3>
    <p class="mb-4 text-base-content/80">
        All reports can be generated with custom filters and exported in multiple formats.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-funnel" class="w-5 h-5 text-primary" />
                    Custom Filters
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Date range picker</li>
                    <li>Multiple category selection</li>
                    <li>Customer groups</li>
                    <li>Product filters</li>
                    <li>Staff/cashier filters</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-document-arrow-down" class="w-5 h-5 text-primary" />
                    Export Options
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>PDF for presentations</li>
                    <li>Excel for analysis</li>
                    <li>CSV for data import</li>
                    <li>Print-friendly formats</li>
                    <li>Email delivery</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-clock" class="w-5 h-5 text-primary" />
                    Scheduling
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Daily automated reports</li>
                    <li>Weekly summaries</li>
                    <li>Monthly performance</li>
                    <li>Email distribution</li>
                    <li>Custom schedules</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Dashboard Analytics</h3>
    <p class="mb-4 text-base-content/80">
        The main dashboard provides real-time key performance indicators (KPIs) at a glance.
    </p>

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-primary">
                <x-mary-icon name="o-banknotes" class="w-8 h-8" />
            </div>
            <div class="stat-title">Today's Sales</div>
            <div class="stat-value text-primary">₱12,345</div>
            <div class="stat-desc">↗︎ 12% (compared to yesterday)</div>
        </div>

        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-secondary">
                <x-mary-icon name="o-shopping-cart" class="w-8 h-8" />
            </div>
            <div class="stat-title">Transactions</div>
            <div class="stat-value text-secondary">45</div>
            <div class="stat-desc">↗︎ 8% (vs. yesterday)</div>
        </div>

        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-accent">
                <x-mary-icon name="o-exclamation-triangle" class="w-8 h-8" />
            </div>
            <div class="stat-title">Low Stock Items</div>
            <div class="stat-value text-accent">8</div>
            <div class="stat-desc">Requires attention</div>
        </div>

        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-info">
                <x-mary-icon name="o-users" class="w-8 h-8" />
            </div>
            <div class="stat-title">Active Customers</div>
            <div class="stat-value text-info">1,234</div>
            <div class="stat-desc">↗︎ 15 new this week</div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Report Best Practices</h3>
    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Daily Reporting</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Review daily sales summary</li>
                    <li>Check low stock alerts</li>
                    <li>Monitor cashier performance</li>
                    <li>Verify cash drawer balances</li>
                    <li>Track payment method trends</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Weekly Analysis</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Product performance review</li>
                    <li>Customer acquisition analysis</li>
                    <li>Inventory turnover rates</li>
                    <li>Profit margin trends</li>
                    <li>Supplier performance metrics</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Key Performance Indicators (KPIs)</h3>
    <p class="mb-4 text-base-content/80">
        Monitor these essential metrics to gauge business health and growth.
    </p>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>KPI</th>
                    <th>Formula</th>
                    <th>Good Range</th>
                    <th>Review Frequency</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gross Profit Margin</td>
                    <td>(Revenue - COGS) / Revenue × 100</td>
                    <td>30-50%</td>
                    <td>Monthly</td>
                </tr>
                <tr>
                    <td>Inventory Turnover</td>
                    <td>COGS / Average Inventory</td>
                    <td>6-12 times/year</td>
                    <td>Quarterly</td>
                </tr>
                <tr>
                    <td>Average Transaction Value</td>
                    <td>Total Sales / Number of Transactions</td>
                    <td>Track trends</td>
                    <td>Weekly</td>
                </tr>
                <tr>
                    <td>Customer Retention Rate</td>
                    <td>Returning Customers / Total Customers × 100</td>
                    <td>>70%</td>
                    <td>Monthly</td>
                </tr>
                <tr>
                    <td>Stock Accuracy</td>
                    <td>Accurate Counts / Total Counts × 100</td>
                    <td>>95%</td>
                    <td>After cycle counts</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Automated Alerts & Notifications</h3>
    <p class="mb-4 text-base-content/80">
        Set up automated alerts to stay informed about critical business events.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
        <div class="card bg-warning text-warning-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-exclamation-triangle" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Low Stock Alerts</h4>
                <p class="text-sm opacity-90">When inventory falls below minimum levels</p>
            </div>
        </div>
        <div class="card bg-error text-error-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-shield-exclamation" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Warranty Expiry</h4>
                <p class="text-sm opacity-90">Products with expiring warranties</p>
            </div>
        </div>
        <div class="card bg-info text-info-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-chart-bar" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Sales Targets</h4>
                <p class="text-sm opacity-90">Progress towards monthly goals</p>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Report Troubleshooting</h3>
    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Common Issues & Solutions</h4>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="report-issues" />
                <div class="text-lg font-medium collapse-title">
                    Reports are slow to load
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Causes:</strong> Large date ranges, complex filters</p>
                        <p><strong>Solutions:</strong> Reduce date range, simplify filters, run during off-peak hours
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="report-issues" />
                <div class="text-lg font-medium collapse-title">
                    Data doesn't match expectations
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Check:</strong> Date filters, product categories, user permissions</p>
                        <p><strong>Verify:</strong> Time zones, currency settings, calculation methods</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="report-issues" />
                <div class="text-lg font-medium collapse-title">
                    Export fails or is incomplete
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Try:</strong> Smaller date ranges, different export format</p>
                        <p><strong>Check:</strong> Browser pop-up blockers, download permissions</p>
                    </div>
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-200">
                <input type="radio" name="report-issues" />
                <div class="text-lg font-medium collapse-title">
                    Missing data in reports
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Verify:</strong> Data entry completeness, system permissions</p>
                        <p><strong>Check:</strong> Filter settings, user access levels, data synchronization</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Advanced Analytics</h3>
    <p class="mb-4 text-base-content/80">
        For deeper insights, export data to Excel for advanced analysis or integration with business intelligence tools.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Excel Integration</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Pivot tables for data analysis</li>
                    <li>Chart creation and visualization</li>
                    <li>Forecasting and trend analysis</li>
                    <li>Custom calculations and formulas</li>
                    <li>Data comparison across periods</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">External Tools</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Power BI integration</li>
                    <li>Google Analytics comparison</li>
                    <li>Accounting software sync</li>
                    <li>Business intelligence platforms</li>
                    <li>Custom dashboard creation</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Report Security & Access</h3>
    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-lock-closed" class="w-6 h-6" />
        <div>
            <strong>Data Protection:</strong> All reports respect user permissions and only show data the user is
            authorized to view.
        </div>
    </div>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>User Role</th>
                    <th>Sales Reports</th>
                    <th>Inventory Reports</th>
                    <th>Financial Reports</th>
                    <th>Customer Reports</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Administrator</td>
                    <td class="text-success">Full Access</td>
                    <td class="text-success">Full Access</td>
                    <td class="text-success">Full Access</td>
                    <td class="text-success">Full Access</td>
                </tr>
                <tr>
                    <td>Manager</td>
                    <td class="text-success">Full Access</td>
                    <td class="text-success">Full Access</td>
                    <td class="text-success">Full Access</td>
                    <td class="text-success">Full Access</td>
                </tr>
                <tr>
                    <td>Cashier</td>
                    <td class="text-warning">Limited</td>
                    <td class="text-error">No Access</td>
                    <td class="text-error">No Access</td>
                    <td class="text-warning">Limited</td>
                </tr>
                <tr>
                    <td>Warehouse Staff</td>
                    <td class="text-error">No Access</td>
                    <td class="text-success">Full Access</td>
                    <td class="text-error">No Access</td>
                    <td class="text-error">No Access</td>
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
                    <li>Review daily sales and inventory reports</li>
                    <li>Set up automated alerts for critical metrics</li>
                    <li>Export data regularly for backup</li>
                    <li>Compare performance across time periods</li>
                    <li>Share relevant reports with team members</li>
                    <li>Use filters to focus on specific insights</li>
                    <li>Schedule regular report generation</li>
                </ul>
            </div>
        </div>
        <div class="card bg-error text-error-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✗ Avoid This</h4>
                <ul class="space-y-1 text-sm">
                    <li>Ignoring low stock alerts</li>
                    <li>Running large reports during peak hours</li>
                    <li>Making decisions without data verification</li>
                    <li>Overlooking trend analysis</li>
                    <li>Sharing sensitive reports inappropriately</li>
                    <li>Relying on outdated report data</li>
                    <li>Not backing up critical reports</li>
                </ul>
            </div>
        </div>
    </div>
</div>
