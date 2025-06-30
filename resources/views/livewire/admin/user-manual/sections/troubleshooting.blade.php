<div class="prose prose-lg max-w-none">
    <h2 class="mb-6 text-2xl font-bold text-base-content">Troubleshooting & FAQ</h2>

    <div class="mb-6 alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Quick Help:</strong> Most issues can be resolved by following the steps in this section. For complex
            problems, contact your system administrator.
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Common Login Issues</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Login Problems & Solutions</h4>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="login-issues" />
                <div class="text-lg font-medium collapse-title">
                    "Invalid email or password" error
                </div>
                <div class="collapse-content">
                    <div class="space-y-3 text-sm">
                        <div class="alert alert-warning">
                            <strong>Check:</strong> Email spelling, password caps lock, account status
                        </div>
                        <div>
                            <p><strong>Steps to resolve:</strong></p>
                            <ol class="mt-2 space-y-1 list-decimal list-inside">
                                <li>Verify email address is spelled correctly</li>
                                <li>Check if Caps Lock is on</li>
                                <li>Try typing password in a text editor to verify</li>
                                <li>Contact admin for password reset</li>
                                <li>Verify account is still active</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="login-issues" />
                <div class="text-lg font-medium collapse-title">
                    Page won't load or keeps refreshing
                </div>
                <div class="collapse-content">
                    <div class="space-y-3 text-sm">
                        <div class="alert alert-info">
                            <strong>Usually:</strong> Browser cache or connectivity issue
                        </div>
                        <div>
                            <p><strong>Try these solutions:</strong></p>
                            <ol class="mt-2 space-y-1 list-decimal list-inside">
                                <li>Clear browser cache and cookies</li>
                                <li>Try a different browser</li>
                                <li>Check internet connection</li>
                                <li>Disable browser extensions temporarily</li>
                                <li>Try incognito/private browsing mode</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-200">
                <input type="radio" name="login-issues" />
                <div class="text-lg font-medium collapse-title">
                    "Access denied" after logging in
                </div>
                <div class="collapse-content">
                    <div class="space-y-3 text-sm">
                        <div class="alert alert-error">
                            <strong>Cause:</strong> Insufficient permissions for requested page
                        </div>
                        <div>
                            <p><strong>Solution:</strong></p>
                            <ol class="mt-2 space-y-1 list-decimal list-inside">
                                <li>Contact administrator to verify your role</li>
                                <li>Check if you're accessing the correct URL</li>
                                <li>Ensure your account has required permissions</li>
                                <li>Try accessing a different section first</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">POS & Sales Issues</h3>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Barcode Scanner Problems</h4>
                <div class="space-y-2 text-sm">
                    <div class="p-2 rounded bg-error/10">
                        <strong>Issue:</strong> Scanner not working
                    </div>
                    <div>
                        <p><strong>Solutions:</strong></p>
                        <ul class="space-y-1 list-disc list-inside">
                            <li>Check USB connection</li>
                            <li>Press <kbd class="kbd kbd-xs">F1</kbd> to open scanner modal</li>
                            <li>Verify scanner settings</li>
                            <li>Test with known working barcode</li>
                            <li>Restart scanner if wireless</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Product Not Found</h4>
                <div class="space-y-2 text-sm">
                    <div class="p-2 rounded bg-warning/10">
                        <strong>Issue:</strong> Product search returns no results
                    </div>
                    <div>
                        <p><strong>Check:</strong></p>
                        <ul class="space-y-1 list-disc list-inside">
                            <li>Product name spelling</li>
                            <li>SKU format (dashes, spaces)</li>
                            <li>Product status (active/inactive)</li>
                            <li>Category filters</li>
                            <li>Warehouse assignments</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Inventory Management Issues</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Stock Level Problems</h4>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Problem</th>
                            <th>Possible Cause</th>
                            <th>Solution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Stock shows negative</td>
                            <td>Sales processed without inventory</td>
                            <td>Run stock adjustment, check sale history</td>
                        </tr>
                        <tr>
                            <td>Stock not updating after sale</td>
                            <td>System sync issue</td>
                            <td>Refresh page, check sale completion</td>
                        </tr>
                        <tr>
                            <td>Incorrect stock levels</td>
                            <td>Manual adjustments, data import errors</td>
                            <td>Physical count, stock adjustment</td>
                        </tr>
                        <tr>
                            <td>Missing stock movements</td>
                            <td>System glitch, user error</td>
                            <td>Check activity logs, recompute totals</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">System Performance Issues</h3>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-clock" class="w-5 h-5 text-warning" />
                    Slow Loading
                </h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Quick Fixes:</strong></p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Clear browser cache</li>
                        <li>Close other browser tabs</li>
                        <li>Check internet speed</li>
                        <li>Restart browser</li>
                        <li>Use Chrome or Firefox</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-x-circle" class="w-5 h-5 text-error" />
                    Page Errors
                </h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Common Solutions:</strong></p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Refresh the page (F5)</li>
                        <li>Go back and try again</li>
                        <li>Check form data</li>
                        <li>Log out and log back in</li>
                        <li>Contact administrator</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-wifi" class="w-5 h-5 text-info" />
                    Connection Issues
                </h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Check:</strong></p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Internet connection</li>
                        <li>Server status</li>
                        <li>Firewall settings</li>
                        <li>VPN connection</li>
                        <li>DNS settings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Printing & Receipt Issues</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Receipt Printing Problems</h4>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h5 class="mb-3 font-semibold text-error">Common Problems</h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded bg-error/10">
                            <x-mary-icon name="o-printer" class="w-4 h-4 text-error" />
                            <span class="text-sm">Printer not responding</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded bg-error/10">
                            <x-mary-icon name="o-document" class="w-4 h-4 text-error" />
                            <span class="text-sm">Blank receipts printing</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded bg-error/10">
                            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4 text-error" />
                            <span class="text-sm">Cut-off text</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded bg-error/10">
                            <x-mary-icon name="o-no-symbol" class="w-4 h-4 text-error" />
                            <span class="text-sm">Print button disabled</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="mb-3 font-semibold text-success">Solutions</h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded bg-success/10">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 text-success" />
                            <span class="text-sm">Check printer connection</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded bg-success/10">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 text-success" />
                            <span class="text-sm">Verify paper loaded correctly</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded bg-success/10">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 text-success" />
                            <span class="text-sm">Adjust print settings</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded bg-success/10">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 text-success" />
                            <span class="text-sm">Check browser pop-up blocker</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Data Entry & Form Issues</h3>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Form Validation Errors</h4>
                <div class="space-y-2 text-sm">
                    <div class="p-2 rounded bg-warning/10">
                        <strong>Common Issues:</strong>
                    </div>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Required fields left empty</li>
                        <li>Invalid email format</li>
                        <li>Duplicate SKU or barcode</li>
                        <li>Price format errors</li>
                        <li>Date format issues</li>
                    </ul>
                    <div class="p-2 mt-3 rounded bg-success/10">
                        <strong>Solution:</strong> Check red error messages, fill all required fields correctly
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">Data Not Saving</h4>
                <div class="space-y-2 text-sm">
                    <div class="p-2 rounded bg-error/10">
                        <strong>Possible Causes:</strong>
                    </div>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Form validation errors</li>
                        <li>Session timeout</li>
                        <li>Network interruption</li>
                        <li>Browser issues</li>
                        <li>Permission problems</li>
                    </ul>
                    <div class="p-2 mt-3 rounded bg-info/10">
                        <strong>Try:</strong> Refresh page, re-login, check permissions
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Network & Connectivity Issues</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Connection Problems</h4>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-wifi" class="w-8 h-8 mx-auto mb-2 text-error" />
                    <h5 class="font-semibold">No Internet</h5>
                    <p class="mb-2 text-sm text-base-content/80">Cannot access system</p>
                    <div class="text-xs">
                        <p><strong>Check:</strong> WiFi, ethernet cable, router status</p>
                    </div>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-signal" class="w-8 h-8 mx-auto mb-2 text-warning" />
                    <h5 class="font-semibold">Slow Connection</h5>
                    <p class="mb-2 text-sm text-base-content/80">Pages load slowly</p>
                    <div class="text-xs">
                        <p><strong>Try:</strong> Reset router, check bandwidth usage</p>
                    </div>
                </div>
                <div class="p-4 text-center border rounded border-base-300">
                    <x-mary-icon name="o-arrow-path" class="w-8 h-8 mx-auto mb-2 text-info" />
                    <h5 class="font-semibold">Intermittent Issues</h5>
                    <p class="mb-2 text-sm text-base-content/80">Connection drops</p>
                    <div class="text-xs">
                        <p><strong>Solution:</strong> Use ethernet instead of WiFi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Frequently Asked Questions</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Common Questions</h4>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    How do I change my password?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Click your name in the top-left corner, select "Profile", then click "Update Password".
                            You'll need your current password to set a new one.</p>
                        <div class="alert alert-info">
                            <strong>Note:</strong> Passwords must be at least 8 characters long and include letters and
                            numbers.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    Can I use the system on my mobile phone?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Yes! The system is fully responsive and works on phones and tablets. Use your mobile browser
                            to access the same URL as on desktop.</p>
                        <div class="alert alert-success">
                            <strong>Best Experience:</strong> Use Chrome or Safari on mobile devices for optimal
                            performance.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    Why can't I see certain menu items?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Menu visibility is based on your user role and permissions. Different roles see different
                            features:</p>
                        <ul class="mt-2 list-disc list-inside">
                            <li><strong>Admin:</strong> Sees everything</li>
                            <li><strong>Manager:</strong> No user management</li>
                            <li><strong>Cashier:</strong> Sales features only</li>
                            <li><strong>Warehouse:</strong> Inventory features only</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    How do I process a return?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Go to <strong>Sales > Returns & Exchanges</strong>, search for the original sale, select
                            items to return, choose return reason, and complete the return process.</p>
                        <div class="alert alert-warning">
                            <strong>Remember:</strong> Returns affect inventory levels and should be processed promptly.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    What if I accidentally delete something?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Most data in the system is not permanently deleted but marked as inactive. Contact your
                            administrator to restore deleted items if needed.</p>
                        <div class="alert alert-info">
                            <strong>Prevention:</strong> The system usually asks for confirmation before deleting
                            anything important.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    Can I export data to Excel?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Yes! Most reports and data views have export buttons. Look for "Export" or "Download"
                            buttons, usually near the top-right of data tables.</p>
                        <div class="alert alert-success">
                            <strong>Tip:</strong> You can export in multiple formats: Excel, CSV, and PDF depending on
                            the report type.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    How do I add products in bulk?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Navigate to <strong>Inventory > Products</strong> and look for the "Import" button. You can
                            upload CSV or Excel files with product information.</p>
                        <div class="alert alert-info">
                            <strong>Template:</strong> Download the sample template first to ensure proper formatting.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    What are the POS keyboard shortcuts?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>The POS system includes several keyboard shortcuts for faster operation:</p>
                        <ul class="mt-2 space-y-1 list-disc list-inside">
                            <li><kbd class="kbd kbd-xs">F1</kbd> - Open Barcode Scanner</li>
                            <li><kbd class="kbd kbd-xs">F2</kbd> - Complete Sale (Payment)</li>
                            <li><kbd class="kbd kbd-xs">F3</kbd> - Clear Cart</li>
                            <li><kbd class="kbd kbd-xs">F4</kbd> - Hold Sale</li>
                            <li><kbd class="kbd kbd-xs">F5</kbd> - View Held Sales</li>
                            <li><kbd class="kbd kbd-xs">Esc</kbd> - Close Modals</li>
                        </ul>
                        <div class="mt-2 alert alert-info">
                            <strong>Tip:</strong> Shortcuts are shown as small indicators on POS buttons.
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-200">
                <input type="radio" name="faq" />
                <div class="text-lg font-medium collapse-title">
                    Why is my barcode scanner not working?
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Check USB connection, press <kbd class="kbd kbd-xs">F1</kbd> to open scanner modal, and
                            ensure the scanner is configured to add "Enter" after each scan.</p>
                        <div class="alert alert-warning">
                            <strong>Setup:</strong> Some scanners need to be configured for keyboard wedge mode.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Browser Compatibility & Requirements</h3>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title text-success">✓ Recommended Browsers</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span>Google Chrome</span>
                        <span class="badge badge-success">Excellent</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Mozilla Firefox</span>
                        <span class="badge badge-success">Excellent</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Microsoft Edge</span>
                        <span class="badge badge-primary">Good</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Safari (Mac/iOS)</span>
                        <span class="badge badge-primary">Good</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title text-warning">⚠ Not Recommended</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span>Internet Explorer</span>
                        <span class="badge badge-error">Not Supported</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Old Browser Versions</span>
                        <span class="badge badge-warning">Issues</span>
                    </div>
                    <div class="mt-3 text-xs text-base-content/70">
                        <strong>Note:</strong> Always use the latest browser version for best security and performance.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Getting Additional Help</h3>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
        <div class="card bg-primary text-primary-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-book-open" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">User Manual</h4>
                <p class="text-sm opacity-90">Search this manual for detailed instructions</p>
                <button class="mt-2 btn btn-sm btn-outline"
                    onclick="document.querySelector('input[wire\\:model\\.live=\"searchTerm\"]').focus()">
                    Search Manual
                </button>
            </div>
        </div>
        <div class="card bg-secondary text-secondary-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-users" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Ask Colleagues</h4>
                <p class="text-sm opacity-90">Other users may have encountered similar issues</p>
                <div class="mt-2 text-xs opacity-75">
                    Check with experienced team members
                </div>
            </div>
        </div>
        <div class="card bg-accent text-accent-content">
            <div class="p-4 text-center card-body">
                <x-mary-icon name="o-phone" class="w-8 h-8 mx-auto mb-2" />
                <h4 class="font-semibold">Contact Admin</h4>
                <p class="text-sm opacity-90">For technical issues and access problems</p>
                <div class="mt-2 text-xs opacity-75">
                    Include error messages and screenshots
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Error Reporting Best Practices</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">When Reporting Issues, Include:</h4>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h5 class="mb-3 font-semibold">Essential Information</h5>
                    <ul class="space-y-1 text-sm text-base-content/80">
                        <li>• What you were trying to do</li>
                        <li>• What actually happened</li>
                        <li>• Exact error message (if any)</li>
                        <li>• Time when error occurred</li>
                        <li>• Your user role/permissions</li>
                        <li>• Browser and version</li>
                        <li>• Steps to reproduce the issue</li>
                    </ul>
                </div>
                <div>
                    <h5 class="mb-3 font-semibold">Helpful Additions</h5>
                    <ul class="space-y-1 text-sm text-base-content/80">
                        <li>• Workarounds you've tried</li>
                        <li>• Impact on your work</li>
                        <li>• Urgency level</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Quick Fixes Checklist</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Try These Steps First</h4>

            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Refresh the page (F5 or Ctrl+R)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Clear browser cache and cookies</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Try a different browser</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Check internet connection</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Log out and log back in</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Restart your computer/device</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Try accessing from a different device</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Disable browser extensions temporarily</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Check for system maintenance notifications</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="text-base-content">Verify date and time settings on your device</span>
                </label>
            </div>

            <div class="mt-4 alert alert-success">
                <x-mary-icon name="o-light-bulb" class="w-6 h-6" />
                <div>
                    <strong>Pro Tip:</strong> These simple steps resolve about 80% of common issues. Always try them
                    before contacting support.
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Hardware Troubleshooting</h3>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-printer" class="w-5 h-5 text-primary" />
                    Receipt Printer Issues
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" />
                        <div class="text-sm font-medium collapse-title">
                            Printer Won't Print
                        </div>
                        <div class="text-xs collapse-content">
                            <ul class="space-y-1 list-disc list-inside">
                                <li>Check power cable connection</li>
                                <li>Verify USB/network cable</li>
                                <li>Check paper roll installation</li>
                                <li>Look for error lights on printer</li>
                                <li>Restart printer and computer</li>
                            </ul>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" />
                        <div class="text-sm font-medium collapse-title">
                            Garbled or Faded Print
                        </div>
                        <div class="text-xs collapse-content">
                            <ul class="space-y-1 list-disc list-inside">
                                <li>Replace thermal paper roll</li>
                                <li>Clean printer head</li>
                                <li>Check print density settings</li>
                                <li>Verify paper quality</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow-sm card bg-base-100">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-qr-code" class="w-5 h-5 text-primary" />
                    Barcode Scanner Issues
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" />
                        <div class="text-sm font-medium collapse-title">
                            Scanner Not Reading
                        </div>
                        <div class="text-xs collapse-content">
                            <ul class="space-y-1 list-disc list-inside">
                                <li>Clean scanner lens with soft cloth</li>
                                <li>Check USB connection</li>
                                <li>Verify barcode quality</li>
                                <li>Adjust scanner distance</li>
                                <li>Test with known good barcode</li>
                            </ul>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" />
                        <div class="text-sm font-medium collapse-title">
                            Wrong Characters Appearing
                        </div>
                        <div class="text-xs collapse-content">
                            <ul class="space-y-1 list-disc list-inside">
                                <li>Check scanner configuration</li>
                                <li>Verify barcode type settings</li>
                                <li>Reset scanner to defaults</li>
                                <li>Update scanner drivers</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Performance Optimization Tips</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">Speed Up Your System</h4>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="p-4 border rounded border-primary/20 bg-primary/5">
                    <h5 class="mb-2 font-semibold text-primary">Browser Optimization</h5>
                    <ul class="space-y-1 text-sm">
                        <li>• Close unused tabs</li>
                        <li>• Clear cache weekly</li>
                        <li>• Disable unnecessary extensions</li>
                        <li>• Update browser regularly</li>
                        <li>• Use bookmarks for quick access</li>
                    </ul>
                </div>
                <div class="p-4 border rounded border-secondary/20 bg-secondary/5">
                    <h5 class="mb-2 font-semibold text-secondary">Network Optimization</h5>
                    <ul class="space-y-1 text-sm">
                        <li>• Use wired connection when possible</li>
                        <li>• Position WiFi router centrally</li>
                        <li>• Limit bandwidth-heavy apps</li>
                        <li>• Check for network congestion</li>
                        <li>• Update network drivers</li>
                    </ul>
                </div>
                <div class="p-4 border rounded border-accent/20 bg-accent/5">
                    <h5 class="mb-2 font-semibold text-accent">System Maintenance</h5>
                    <ul class="space-y-1 text-sm">
                        <li>• Restart computer daily</li>
                        <li>• Keep OS updated</li>
                        <li>• Free up disk space</li>
                        <li>• Run virus scans</li>
                        <li>• Check for malware</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Emergency Procedures</h3>

    <div class="mb-6 alert alert-error">
        <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
        <div>
            <strong>System Down Emergency:</strong> If the system is completely inaccessible, use manual sales recording
            until service is restored.
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
        <div class="card bg-error text-error-content">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-exclamation-circle" class="w-5 h-5" />
                    System Outage
                </h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Immediate Actions:</strong></p>
                    <ol class="space-y-1 list-decimal list-inside">
                        <li>Switch to manual sales recording</li>
                        <li>Use backup calculator for totals</li>
                        <li>Issue handwritten receipts</li>
                        <li>Note customer details manually</li>
                        <li>Contact system administrator</li>
                        <li>Enter sales data when system returns</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card bg-warning text-warning-content">
            <div class="p-4 card-body">
                <h4 class="text-lg card-title">
                    <x-mary-icon name="o-shield-exclamation" class="w-5 h-5" />
                    Data Loss Prevention
                </h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Best Practices:</strong></p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Save work frequently</li>
                        <li>Don't close browser during saves</li>
                        <li>Keep backup of important data</li>
                        <li>Verify saves completed successfully</li>
                        <li>Export critical reports regularly</li>
                        <li>Use "Save Draft" features when available</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 text-xl font-semibold text-base-content">Advanced Troubleshooting</h3>

    <div class="mb-6 shadow-sm card bg-base-100">
        <div class="p-6 card-body">
            <h4 class="mb-4 text-lg card-title">For Technical Users</h4>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="advanced-troubleshooting" />
                <div class="text-lg font-medium collapse-title">
                    Browser Console Errors
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Press F12 to open developer tools, check Console tab for JavaScript errors.</p>
                        <div class="p-2 font-mono text-xs rounded bg-neutral text-neutral-content">
                            Common errors: Failed to load resource, Uncaught TypeError, CORS errors
                        </div>
                        <p><strong>Action:</strong> Screenshot console errors and report to administrator.</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 collapse collapse-arrow bg-base-200">
                <input type="radio" name="advanced-troubleshooting" />
                <div class="text-lg font-medium collapse-title">
                    Network Tab Analysis
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Use Network tab in developer tools to check for failed requests.</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2 rounded bg-success/10">
                                <strong>Good:</strong> Status 200-299
                            </div>
                            <div class="p-2 rounded bg-error/10">
                                <strong>Bad:</strong> Status 400-599
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-200">
                <input type="radio" name="advanced-troubleshooting" />
                <div class="text-lg font-medium collapse-title">
                    Local Storage Issues
                </div>
                <div class="collapse-content">
                    <div class="space-y-2 text-sm">
                        <p>Clear local storage if experiencing persistent issues:</p>
                        <ol class="space-y-1 list-decimal list-inside">
                            <li>Open developer tools (F12)</li>
                            <li>Go to Application tab</li>
                            <li>Select Local Storage</li>
                            <li>Delete relevant entries</li>
                            <li>Refresh page</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-6 h-6" />
        <div>
            <strong>Remember:</strong> This system is designed to be user-friendly. Most functions have built-in help
            and confirmation dialogs. Don't hesitate to explore and try different options! When in doubt, the search
            function at the top of this manual can help you find specific information quickly.
        </div>
    </div>
</div>
