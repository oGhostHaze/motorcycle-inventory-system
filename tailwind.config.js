import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./vendor/robsontenorio/mary/src/View/Components/**/*.php"
    ],

    safelist: [
        // === ESSENTIAL CLASSES FOR MOTORCYCLE INVENTORY SYSTEM ===

        // Stock Status Indicators (dynamically applied based on inventory levels)
        'text-success', 'text-warning', 'text-error', 'text-info',
        'bg-success/10', 'bg-warning/10', 'bg-error/10', 'bg-info/10',
        'border-success', 'border-warning', 'border-error', 'border-info',

        // Badge variants for status indicators
        'badge-success', 'badge-warning', 'badge-error', 'badge-info', 'badge-neutral',
        'badge-primary', 'badge-secondary', 'badge-accent',
        'badge-xs', 'badge-sm', 'badge-md', 'badge-lg',
        'badge-outline', 'badge-ghost',

        // Button variants (for different user roles and actions)
        'btn-primary', 'btn-secondary', 'btn-success', 'btn-warning', 'btn-error',
        'btn-info', 'btn-accent', 'btn-neutral', 'btn-ghost', 'btn-outline',
        'btn-xs', 'btn-sm', 'btn-md', 'btn-lg', 'btn-circle',

        // Alert variants for notifications
        'alert-info', 'alert-success', 'alert-warning', 'alert-error',

        // Theme gradient previews (for theme switcher)
        'from-gray-100', 'to-white',           // Light theme
        'from-gray-800', 'to-black',           // Dark theme
        'from-pink-200', 'to-purple-200',     // Cupcake theme
        'from-blue-600', 'to-blue-800',       // Corporate theme
        'from-purple-600', 'to-pink-600',     // Synthwave theme
        'from-yellow-400', 'to-orange-400',   // Retro theme
        'from-yellow-400', 'to-pink-500',     // Cyberpunk theme
        'from-pink-300', 'to-red-300',        // Valentine theme

        // Inventory-specific color indicators
        'text-green-500', 'text-green-600', 'text-green-700',  // In stock
        'text-red-500', 'text-red-600', 'text-red-700',        // Out of stock
        'text-yellow-500', 'text-yellow-600', 'text-yellow-700', // Low stock
        'text-blue-500', 'text-blue-600', 'text-blue-700',     // Overstock

        // Background colors for inventory cards/sections
        'bg-green-50', 'bg-red-50', 'bg-yellow-50', 'bg-blue-50',
        'bg-primary/10', 'bg-secondary/10', 'bg-accent/10',

        // Hover states for interactive elements
        'hover:bg-primary', 'hover:bg-secondary', 'hover:bg-accent',
        'hover:text-primary', 'hover:text-white', 'hover:border-primary',

        // Form input variants
        'input-primary', 'input-secondary', 'input-success', 'input-warning', 'input-error',
        'input-bordered', 'input-xs', 'input-sm', 'input-md', 'input-lg',

        // Table classes for data display
        'table-zebra', 'table-compact', 'table-normal',

        // Modal and dropdown states
        'modal-open', 'dropdown-open', 'drawer-open',

        // Loading states
        'loading', 'loading-spinner', 'loading-dots',

        // Tooltip variants
        'tooltip', 'tooltip-primary', 'tooltip-success', 'tooltip-warning', 'tooltip-error',
        'tooltip-top', 'tooltip-bottom', 'tooltip-left', 'tooltip-right',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        typography,
        require("daisyui")
    ],
};
