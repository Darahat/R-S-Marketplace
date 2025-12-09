<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Theme Colors Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all theme-related configurations that can be used
    | across the entire application. Change these values to update the
    | theme globally.
    |
    */

    'colors' => [
        // Primary brand colors
        'primary' => [
            'DEFAULT' => '#3b82f6',  // Blue 500
            'light' => '#60a5fa',    // Blue 400
            'dark' => '#2563eb',     // Blue 600
            'hover' => '#1d4ed8',    // Blue 700
        ],

        // Secondary/Accent colors
        'secondary' => [
            'DEFAULT' => '#8b5cf6',  // Violet 500
            'light' => '#a78bfa',    // Violet 400
            'dark' => '#7c3aed',     // Violet 600
        ],

        // Success color
        'success' => [
            'DEFAULT' => '#10b981',  // Emerald 500
            'light' => '#34d399',    // Emerald 400
            'dark' => '#059669',     // Emerald 600
        ],

        // Warning color
        'warning' => [
            'DEFAULT' => '#f59e0b',  // Amber 500
            'light' => '#fbbf24',    // Amber 400
            'dark' => '#d97706',     // Amber 600
        ],

        // Danger/Error color
        'danger' => [
            'DEFAULT' => '#ef4444',  // Red 500
            'light' => '#f87171',    // Red 400
            'dark' => '#dc2626',     // Red 600
        ],

        // Neutral colors
        'neutral' => [
            'white' => '#ffffff',
            'black' => '#000000',
            'gray-50' => '#f9fafb',
            'gray-100' => '#f3f4f6',
            'gray-200' => '#e5e7eb',
            'gray-300' => '#d1d5db',
            'gray-400' => '#9ca3af',
            'gray-500' => '#6b7280',
            'gray-600' => '#4b5563',
            'gray-700' => '#374151',
            'gray-800' => '#1f2937',
            'gray-900' => '#111827',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Typography
    |--------------------------------------------------------------------------
    */
    'typography' => [
        'font_family' => [
            'sans' => "'Inter', 'Segoe UI', sans-serif",
            'mono' => "'Fira Code', monospace",
        ],
        'font_sizes' => [
            'xs' => '0.75rem',     // 12px
            'sm' => '0.875rem',    // 14px
            'base' => '1rem',      // 16px
            'lg' => '1.125rem',    // 18px
            'xl' => '1.25rem',     // 20px
            '2xl' => '1.5rem',     // 24px
            '3xl' => '1.875rem',   // 30px
            '4xl' => '2.25rem',    // 36px
            '5xl' => '3rem',       // 48px
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Spacing & Layout
    |--------------------------------------------------------------------------
    */
    'spacing' => [
        'container_max_width' => '1280px',
        'section_padding_y' => [
            'mobile' => '3rem',    // py-12
            'desktop' => '5rem',   // py-20
        ],
        'card_padding' => '1rem',  // p-4
        'gap' => [
            'xs' => '0.5rem',
            'sm' => '1rem',
            'md' => '1.5rem',
            'lg' => '2rem',
            'xl' => '3rem',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Border Radius
    |--------------------------------------------------------------------------
    */
    'radius' => [
        'none' => '0',
        'sm' => '0.25rem',
        'DEFAULT' => '0.5rem',
        'md' => '0.75rem',
        'lg' => '1rem',
        'xl' => '1.5rem',
        'full' => '9999px',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shadows
    |--------------------------------------------------------------------------
    */
    'shadows' => [
        'sm' => '0 1px 2px 0 rgb(0 0 0 / 0.05)',
        'DEFAULT' => '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
        'md' => '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
        'lg' => '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
        'xl' => '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transitions
    |--------------------------------------------------------------------------
    */
    'transitions' => [
        'fast' => '150ms',
        'DEFAULT' => '300ms',
        'slow' => '500ms',
    ],

    /*
    |--------------------------------------------------------------------------
    | Breakpoints (for reference)
    |--------------------------------------------------------------------------
    */
    'breakpoints' => [
        'sm' => '640px',
        'md' => '768px',
        'lg' => '1024px',
        'xl' => '1280px',
        '2xl' => '1536px',
    ],
];
