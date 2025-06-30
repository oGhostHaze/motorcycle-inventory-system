<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">Administration</h2>

    <div class="mb-6 alert alert-error">
        <x-mary-icon name="o-shield-exclamation" class="w-6 h-6" />
        <div>
            <strong>Admin Access Only:</strong> This section is only accessible to users with Administrator role.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">User Management</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Administration > User Management</strong> to manage system users and their permissions.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-user-plus" class="w-5 h-5 text-primary" />
                    Adding Users
                </h4>
                <div class="space-y-2 text-sm">
                    <div><strong>Required:</strong> Name, Email, Role</div>
                    <div><strong>Roles:</strong> Admin, Manager, Cashier, Warehouse Staff</div>
                    <div><strong>Password:</strong> System generates secure password</div>
                    <div><strong>Status:</strong> Active/Inactive control</div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-key" class="w-5 h-5 text-primary" />
                    Permission System
                </h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Role-based access control</li>
                    <li>Feature-level permissions</li>
                    <li>Data access restrictions</li>
                    <li>Module visibility control</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mb-6 overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Key Permissions</th>
                    <th>Typical Use Case</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-error">Administrator</span></td>
                    <td>Full system access, user management, system settings</td>
                    <td>System owner, IT administrator</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">Manager</span></td>
                    <td>All operations, reports, no user management</td>
                    <td>Store manager, operations supervisor</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">Cashier</span></td>
                    <td>POS, sales history, customer management</td>
                    <td>Sales staff, front desk personnel</td>
                </tr>
                <tr>
                    <td><span class="badge badge-success">Warehouse Staff</span></td>
                    <td>Inventory management, stock operations</td>
                    <td>Warehouse workers, stock clerks</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">System Settings</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Administration > System Settings</strong> to configure global system preferences.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Configuration Categories</h4>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="card bg-primary text-primary-content">
                    <div class="p-4 text-center card-body">
                        <x-mary-icon name="o-building-storefront" class="w-8 h-8 mx-auto mb-2" />
                        <h5 class="font-semibold">Business Info</h5>
                        <p class="text-sm opacity-90">Company details, address, contact</p>
                    </div>
                </div>
                <div class="card bg-secondary text-secondary-content">
                    <div class="p-4 text-center card-body">
                        <x-mary-icon name="o-currency-dollar" class="w-8 h-8 mx-auto mb-2" />
                        <h5 class="font-semibold">Currency & Tax</h5>
                        <p class="text-sm opacity-90">Tax rates, currency format</p>
                    </div>
                </div>
                <div class="card bg-accent text-accent-content">
                    <div class="p-4 text-center card-body">
                        <x-mary-icon name="o-printer" class="w-8 h-8 mx-auto mb-2" />
                        <h5 class="font-semibold">Receipt Settings</h5>
                        <p class="text-sm opacity-90">Receipt format, footer text</p>
                    </div>
                </div>
                <div class="card bg-info text-info-content">
                    <div class="p-4 text-center card-body">
                        <x-mary-icon name="o-bell" class="w-8 h-8 mx-auto mb-2" />
                        <h5 class="font-semibold">Notifications</h5>
                        <p class="text-sm opacity-90">Email alerts, SMS settings</p>
                    </div>
                </div>
                <div class="card bg-warning text-warning-content">
                    <div class="p-4 text-center card-body">
                        <x-mary-icon name="o-shield-check" class="w-8 h-8 mx-auto mb-2" />
                        <h5 class="font-semibold">Security</h5>
                        <p class="text-sm opacity-90">Password policies, session timeout</p>
                    </div>
                </div>
                <div class="card bg-success text-success-content">
                    <div class="p-4 text-center card-body">
                        <x-mary-icon name="o-cog-6-tooth" class="w-8 h-8 mx-auto mb-2" />
                        <h5 class="font-semibold">System Defaults</h5>
                        <p class="text-sm opacity-90">Default values, preferences</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Activity Logs & Audit Trail</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Administration > Activity Logs</strong> to monitor user actions and system events.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Tracked Activities</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>User login/logout events</li>
                    <li>Product creation/modification</li>
                    <li>Sales transactions</li>
                    <li>Stock adjustments</li>
                    <li>Price changes</li>
                    <li>User management actions</li>
                    <li>System configuration changes</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Log Information</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>User who performed action</li>
                    <li>Date and time stamp</li>
                    <li>Action description</li>
                    <li>Before/after values</li>
                    <li>IP address</li>
                    <li>Browser/device info</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Database Backup & Maintenance</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Administration > Database Backup</strong> to manage data backups and system maintenance.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Backup Operations</h4>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-server" class="w-8 h-8 mx-auto mb-2 text-primary" />
                    <h5 class="font-semibold">Manual Backup</h5>
                    <p class="text-sm text-base-content/80">Create immediate backup</p>
                    <button class="mt-2 btn btn-primary btn-sm">Create Backup</button>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-clock" class="w-8 h-8 mx-auto mb-2 text-secondary" />
                    <h5 class="font-semibold">Scheduled Backup</h5>
                    <p class="text-sm text-base-content/80">Automatic daily backups</p>
                    <button class="mt-2 btn btn-secondary btn-sm">Configure Schedule</button>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-arrow-down-tray" class="w-8 h-8 mx-auto mb-2 text-accent" />
                    <h5 class="font-semibold">Download Backup</h5>
                    <p class="text-sm text-base-content/80">Export backup files</p>
                    <button class="mt-2 btn btn-accent btn-sm">Download</button>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 alert alert-warning">
        <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
        <div>
            <strong>Backup Best Practices:</strong> Schedule daily backups, store copies off-site, test restore
            procedures regularly, and keep at least 30 days of backup history.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Data Recompute & Maintenance</h3>
    <p class="mb-4 text-base-content/80">
        Navigate to <strong>Administration > Recompute Totals</strong> to fix data inconsistencies and recalculate
        system totals.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Maintenance Operations</h4>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Operation</th>
                            <th>Purpose</th>
                            <th>When to Use</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Recompute Sales Totals</td>
                            <td>Fix sale total discrepancies</td>
                            <td>After data import or corruption</td>
                            <td>5-10 minutes</td>
                        </tr>
                        <tr>
                            <td>Recalculate Inventory</td>
                            <td>Fix stock level inconsistencies</td>
                            <td>After stock adjustments</td>
                            <td>10-20 minutes</td>
                        </tr>
                        <tr>
                            <td>Update Product Costs</td>
                            <td>Recalculate average costs</td>
                            <td>After bulk price updates</td>
                            <td>5-15 minutes</td>
                        </tr>
                        <tr>
                            <td>Rebuild Search Index</td>
                            <td>Fix search functionality</td>
                            <td>When search is slow</td>
                            <td>2-5 minutes</td>
                        </tr>
                        <tr>
                            <td>Clean Temporary Data</td>
                            <td>Remove old temporary files</td>
                            <td>Monthly maintenance</td>
                            <td>1-2 minutes</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">System Monitoring</h3>
    <p class="mb-4 text-base-content/80">
        Monitor system health and performance through the administration dashboard.
    </p>

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-primary">
                <x-mary-icon name="o-server" class="w-8 h-8" />
            </div>
            <div class="stat-title">Disk Usage</div>
            <div class="stat-value text-primary">2.3GB</div>
            <div class="stat-desc">of 10GB available</div>
        </div>

        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-secondary">
                <x-mary-icon name="o-users" class="w-8 h-8" />
            </div>
            <div class="stat-title">Active Users</div>
            <div class="stat-value text-secondary">12</div>
            <div class="stat-desc">online now</div>
        </div>

        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-accent">
                <x-mary-icon name="o-clock" class="w-8 h-8" />
            </div>
            <div class="stat-title">System Uptime</div>
            <div class="stat-value text-accent">99.9%</div>
            <div class="stat-desc">this month</div>
        </div>

        <div class="rounded-lg stat bg-base-200">
            <div class="stat-figure text-info">
                <x-mary-icon name="o-arrow-down-tray" class="w-8 h-8" />
            </div>
            <div class="stat-title">Last Backup</div>
            <div class="stat-value text-info">2 hrs</div>
            <div class="stat-desc">ago</div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Security Management</h3>
    <p class="mb-4 text-base-content/80">
        Implement and monitor security measures to protect your system and data.
    </p>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Security Features</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Password complexity requirements</li>
                    <li>Session timeout controls</li>
                    <li>Failed login attempt monitoring</li>
                    <li>IP address restrictions</li>
                    <li>Two-factor authentication (optional)</li>
                    <li>Data encryption at rest</li>
                </ul>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Security Monitoring</h4>
                <ul class="space-y-1 text-sm text-base-content/80">
                    <li>Login attempt logs</li>
                    <li>Suspicious activity alerts</li>
                    <li>User access patterns</li>
                    <li>Data access auditing</li>
                    <li>System vulnerability scanning</li>
                    <li>Regular security updates</li>
                </ul>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Data Import & Export</h3>
    <p class="mb-4 text-base-content/80">
        Manage bulk data operations for products, customers, and other system data.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Supported Operations</h4>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h5 class="mb-3 font-semibold text-success">Import Operations</h5>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 rounded bg-success/10">
                            <span class="text-sm">Product Catalog</span>
                            <span class="badge badge-success">CSV, Excel</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-success/10">
                            <span class="text-sm">Customer Database</span>
                            <span class="badge badge-success">CSV, Excel</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-success/10">
                            <span class="text-sm">Supplier Information</span>
                            <span class="badge badge-success">CSV, Excel</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-success/10">
                            <span class="text-sm">Price Updates</span>
                            <span class="badge badge-success">CSV, Excel</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="mb-3 font-semibold text-primary">Export Operations</h5>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 rounded bg-primary/10">
                            <span class="text-sm">Complete Product List</span>
                            <span class="badge badge-primary">Excel, CSV</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-primary/10">
                            <span class="text-sm">Sales Transaction Data</span>
                            <span class="badge badge-primary">Excel, CSV</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-primary/10">
                            <span class="text-sm">Customer Information</span>
                            <span class="badge badge-primary">Excel, CSV</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-primary/10">
                            <span class="text-sm">Inventory Reports</span>
                            <span class="badge badge-primary">Excel, PDF</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">System Updates & Maintenance</h3>
    <p class="mb-4 text-base-content/80">
        Manage system updates and maintenance schedules to keep your system secure and efficient.
    </p>

    <div class="mb-6 timeline">
        <div class="timeline-item">
            <div class="timeline-middle">
                <x-mary-icon name="o-check-circle" class="w-5 h-5 text-success" />
            </div>
            <div class="mb-10 timeline-start md:text-end">
                <time class="font-mono italic">Daily</time>
                <div class="text-lg font-black">Automated Backups</div>
                <div class="text-sm">System creates automatic backups of all data</div>
            </div>
            <hr class="bg-success" />
        </div>
        <div class="timeline-item">
            <hr class="bg-primary" />
            <div class="timeline-middle">
                <x-mary-icon name="o-cog-6-tooth" class="w-5 h-5 text-primary" />
            </div>
            <div class="mb-10 timeline-end">
                <time class="font-mono italic">Weekly</time>
                <div class="text-lg font-black">Performance Monitoring</div>
                <div class="text-sm">Check system performance and optimize as needed</div>
            </div>
            <hr class="bg-secondary" />
        </div>
        <div class="timeline-item">
            <hr class="bg-secondary" />
            <div class="timeline-middle">
                <x-mary-icon name="o-shield-check" class="w-5 h-5 text-secondary" />
            </div>
            <div class="mb-10 timeline-start md:text-end">
                <time class="font-mono italic">Monthly</time>
                <div class="text-lg font-black">Security Updates</div>
                <div class="text-sm">Apply security patches and system updates</div>
            </div>
            <hr class="bg-accent" />
        </div>
        <div class="timeline-item">
            <hr class="bg-accent" />
            <div class="timeline-middle">
                <x-mary-icon name="o-wrench-screwdriver" class="w-5 h-5 text-accent" />
            </div>
            <div class="mb-10 timeline-end">
                <time class="font-mono italic">Quarterly</time>
                <div class="text-lg font-black">Deep Maintenance</div>
                <div class="text-sm">Comprehensive system cleanup and optimization</div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Troubleshooting & Support</h3>
    <p class="mb-4 text-base-content/80">
        Common administrative issues and their solutions.
    </p>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Common Issues</h4>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="admin-issues" />
                <div class="text-lg font-medium collapse-title">
                    Users cannot log in
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Check:</strong> User account status, password reset needs</p>
                        <p><strong>Verify:</strong> Role permissions, account expiration</p>
                        <p><strong>Solution:</strong> Reset password, activate account, check role settings</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="admin-issues" />
                <div class="text-lg font-medium collapse-title">
                    System is running slowly
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Check:</strong> Server resources, database size, active users</p>
                        <p><strong>Action:</strong> Run maintenance operations, clear temporary files</p>
                        <p><strong>Consider:</strong> Database optimization, server upgrade</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="admin-issues" />
                <div class="text-lg font-medium collapse-title">
                    Data inconsistencies
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Identify:</strong> Nature of inconsistency, affected data</p>
                        <p><strong>Solution:</strong> Run recompute operations, check data import logs</p>
                        <p><strong>Prevention:</strong> Regular backup verification, user training</p>
                    </div>
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-200">
                <input type="radio" name="admin-issues" />
                <div class="text-lg font-medium collapse-title">
                    Backup failures
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p><strong>Check:</strong> Disk space, backup schedule, permissions</p>
                        <p><strong>Monitor:</strong> Backup logs, error messages</p>
                        <p><strong>Action:</strong> Free disk space, verify backup destination</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Emergency Procedures</h3>
    <div class="mb-6 alert alert-error">
        <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
        <div>
            <strong>Emergency Contact:</strong> Keep system administrator contact information readily available for
            critical issues.
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
        <div class="card bg-error text-error-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-shield-exclamation" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Security Breach</h4>
                <p class="text-sm opacity-90">Immediate actions for security incidents</p>
                <div class="mt-3 text-xs">
                    <div>1. Change all passwords</div>
                    <div>2. Check activity logs</div>
                    <div>3. Contact support</div>
                </div>
            </div>
        </div>
        <div class="card bg-warning text-warning-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-server" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">System Failure</h4>
                <p class="text-sm opacity-90">Steps for system recovery</p>
                <div class="mt-3 text-xs">
                    <div>1. Assess the damage</div>
                    <div>2. Restore from backup</div>
                    <div>3. Verify data integrity</div>
                </div>
            </div>
        </div>
        <div class="card bg-info text-info-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-exclamation-circle" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Data Corruption</h4>
                <p class="text-sm opacity-90">Data recovery procedures</p>
                <div class="mt-3 text-xs">
                    <div>1. Stop all operations</div>
                    <div>2. Identify corruption scope</div>
                    <div>3. Restore affected data</div>
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
                    <li>Regular backup verification</li>
                    <li>Monitor system performance daily</li>
                    <li>Keep user accounts updated</li>
                    <li>Document all configuration changes</li>
                    <li>Regular security audits</li>
                    <li>Train users on security practices</li>
                    <li>Maintain emergency procedures</li>
                    <li>Schedule regular maintenance</li>
                </ul>
            </div>
        </div>
        <div class="card bg-error text-error-content">
            <div class="p-4 card-body">
                <h4 class="card-title">✗ Avoid This</h4>
                <ul class="space-y-1 text-sm">
                    <li>Sharing admin passwords</li>
                    <li>Skipping backup verification</li>
                    <li>Ignoring security alerts</li>
                    <li>Making changes without documentation</li>
                    <li>Delaying security updates</li>
                    <li>Granting excessive permissions</li>
                    <li>Operating without backups</li>
                    <li>Ignoring performance issues</li>
                </ul>
            </div>
        </div>
    </div>
</div>
