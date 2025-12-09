# Theme Configuration Guide

## Overview

The R-S-Marketplace project now includes a centralized theme configuration system that allows you to easily customize colors, typography, spacing, and other design elements across the entire application.

## Theme Configuration File

The theme configuration is located at: `config/theme.php`

## How to Change Theme Colors

### Method 1: Edit Config File (Recommended)

1. Open `config/theme.php`
2. Modify the color values in the `colors` array:

```php
'colors' => [
    'primary' => [
        'DEFAULT' => '#3b82f6',  // Change this to your brand color
        'light' => '#60a5fa',
        'dark' => '#2563eb',
    ],
    'secondary' => [
        'DEFAULT' => '#8b5cf6',  // Secondary brand color
        'light' => '#a78bfa',
        'dark' => '#7c3aed',
    ],
    // ... other colors
],
```

3. Clear Laravel cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### Method 2: Using Environment Variables

You can also override theme colors using `.env` file:

```env
THEME_PRIMARY_COLOR=#3b82f6
THEME_SECONDARY_COLOR=#8b5cf6
THEME_SUCCESS_COLOR=#10b981
```

Then in `config/theme.php`:

```php
'primary' => [
    'DEFAULT' => env('THEME_PRIMARY_COLOR', '#3b82f6'),
],
```

## Available Theme Configurations

### Colors

-   **Primary**: Main brand color
-   **Secondary**: Secondary/accent color
-   **Success**: Success messages and positive actions
-   **Warning**: Warning messages
-   **Danger**: Error messages and destructive actions
-   **Neutral**: Gray scale colors

### Typography

-   Font families
-   Font sizes (xs, sm, base, lg, xl, 2xl, 3xl, 4xl, 5xl)

### Spacing & Layout

-   Container max width
-   Section padding
-   Card padding
-   Gap sizes

### Border Radius

-   Predefined radius values (sm, DEFAULT, md, lg, xl, full)

### Shadows

-   Shadow levels (sm, DEFAULT, md, lg, xl)

### Transitions

-   Animation speeds (fast, DEFAULT, slow)

## Using Theme in Blade Templates

The theme is automatically integrated into Tailwind CSS configuration in `resources/views/frontend_view/layouts/home.blade.php`.

### Example: Using Primary Color

```html
<button class="bg-primary hover:bg-primary-dark text-white">Click Me</button>
```

### Example: Using CSS Variables

```html
<div style="background-color: var(--color-primary);">Content</div>
```

## Responsive Design

All components are now fully responsive with the following breakpoints:

-   **sm**: 640px (Mobile landscape)
-   **md**: 768px (Tablet)
-   **lg**: 1024px (Desktop)
-   **xl**: 1280px (Large desktop)
-   **2xl**: 1536px (Extra large desktop)

## Component Updates

### 1. Navigation Bar

-   Fully responsive with mobile menu
-   Collapsible search on mobile
-   Smooth animations and transitions
-   Theme-integrated colors

### 2. Product Cards

-   Responsive grid layout (2 columns mobile, 6 columns desktop)
-   Hover effects and animations
-   Optimized images with lazy loading
-   Theme-consistent design

### 3. Section Components

-   Consistent spacing and padding
-   Responsive typography
-   Theme-integrated colors and gradients
-   Better mobile experience

### 4. Hero Section

-   Dynamic gradient backgrounds
-   Responsive text sizes
-   Animated floating elements
-   CTA buttons with hover effects

## Quick Theme Examples

### Blue Theme (Default)

```php
'primary' => ['DEFAULT' => '#3b82f6'],
'secondary' => ['DEFAULT' => '#8b5cf6'],
```

### Green Theme

```php
'primary' => ['DEFAULT' => '#10b981'],
'secondary' => ['DEFAULT' => '#059669'],
```

### Purple Theme

```php
'primary' => ['DEFAULT' => '#8b5cf6'],
'secondary' => ['DEFAULT' => '#a855f7'],
```

### Orange Theme

```php
'primary' => ['DEFAULT' => '#f97316'],
'secondary' => ['DEFAULT' => '#fb923c'],
```

### Red Theme

```php
'primary' => ['DEFAULT' => '#ef4444'],
'secondary' => ['DEFAULT' => '#f87171'],
```

## Best Practices

1. **Consistency**: Always use theme colors instead of hardcoded values
2. **Accessibility**: Ensure sufficient color contrast (WCAG AA minimum)
3. **Testing**: Test your theme on different devices and screen sizes
4. **Performance**: Use Tailwind's JIT mode for optimal CSS generation
5. **Documentation**: Document any custom theme modifications

## Common Customizations

### Change Primary Brand Color

```php
// config/theme.php
'primary' => [
    'DEFAULT' => '#your-color-here',
    'light' => '#lighter-shade',
    'dark' => '#darker-shade',
],
```

### Change Font Family

```php
// config/theme.php
'font_family' => [
    'sans' => "'Your Font', sans-serif",
],
```

### Adjust Container Width

```php
// config/theme.php
'container_max_width' => '1400px',  // Wider container
```

### Modify Section Spacing

```php
// config/theme.php
'section_padding_y' => [
    'mobile' => '4rem',    // More padding on mobile
    'desktop' => '6rem',   // More padding on desktop
],
```

## Troubleshooting

### Theme Changes Not Showing?

1. Clear Laravel cache: `php artisan config:clear`
2. Clear browser cache: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
3. Restart development server

### Colors Not Loading?

1. Verify `config/theme.php` syntax is correct
2. Check that color values are valid hex codes
3. Ensure Tailwind config in `home.blade.php` is loading correctly

## Support

For issues or questions about the theme system, please refer to:

-   Laravel Documentation: https://laravel.com/docs
-   Tailwind CSS Documentation: https://tailwindcss.com/docs

## Version History

-   **v1.0.0** (Dec 2025): Initial theme configuration system
    -   Centralized theme config
    -   Fully responsive design
    -   Component redesign
    -   Mobile-first approach
