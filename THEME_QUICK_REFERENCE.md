# Quick Theme Reference Guide

## 🎨 Using Theme Colors in Your Blade Templates

### 1. Tailwind Classes (Recommended)

```html
<!-- Backgrounds -->
<div class="bg-primary">Primary background</div>
<div class="bg-primary-dark">Primary dark background</div>
<div class="bg-secondary">Secondary background</div>
<div class="bg-success">Success background</div>
<div class="bg-danger">Danger background</div>
<div class="bg-warning">Warning background</div>

<!-- Text Colors -->
<p class="text-primary">Primary text</p>
<p class="text-secondary">Secondary text</p>
<p class="text-success">Success text</p>
<p class="text-danger">Danger text</p>

<!-- Hover States -->
<button class="bg-primary hover:bg-primary-dark">Button</button>
<a class="text-primary hover:text-secondary">Link</a>

<!-- Focus States -->
<input class="focus:ring-primary focus:border-primary" />

<!-- Gradients -->
<div class="bg-gradient-to-r from-primary to-secondary">
    Gradient background
</div>
```

### 2. CSS Variables (For Custom Styles)

```html
<style>
    .custom-element {
        background-color: var(--color-primary);
        color: var(--color-primary-light);
        border-color: var(--color-primary-dark);
    }

    .custom-gradient {
        background: linear-gradient(
            to right,
            var(--color-primary),
            var(--color-secondary)
        );
    }
</style>
```

Available CSS Variables:

-   `--color-primary`
-   `--color-primary-light`
-   `--color-primary-dark`
-   `--color-secondary`
-   `--color-success`
-   `--color-danger`

### 3. Direct Config Access (For Meta Tags, etc.)

```html
<!-- Meta theme color -->
<meta
    name="theme-color"
    content="{{ config('theme.colors.primary.DEFAULT') }}"
/>

<!-- Inline styles when necessary -->
<div style="background-color: {{ config('theme.colors.primary.DEFAULT') }}">
    Content
</div>
```

## 🎯 Common Use Cases

### Button Styles

```html
<!-- Primary Button -->
<button
    class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition"
>
    Click Me
</button>

<!-- Secondary Button -->
<button
    class="bg-secondary hover:bg-secondary-dark text-white font-semibold py-2 px-4 rounded-lg transition"
>
    Secondary Action
</button>

<!-- Danger Button -->
<button
    class="bg-danger hover:bg-danger-dark text-white font-semibold py-2 px-4 rounded-lg transition"
>
    Delete
</button>

<!-- Outline Button -->
<button
    class="border-2 border-primary text-primary hover:bg-primary hover:text-white font-semibold py-2 px-4 rounded-lg transition"
>
    Outline
</button>
```

### Link Styles

```html
<!-- Primary Link -->
<a href="#" class="text-primary hover:text-primary-dark underline">
    Learn More
</a>

<!-- Navigation Link -->
<a href="#" class="text-gray-700 hover:text-primary font-medium transition">
    Home
</a>
```

### Form Input Styles

```html
<!-- Text Input -->
<input
    type="text"
    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
    placeholder="Enter text"
/>

<!-- Select -->
<select
    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
>
    <option>Option 1</option>
</select>

<!-- Checkbox -->
<input
    type="checkbox"
    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
/>
```

### Alert/Badge Styles

```html
<!-- Success Alert -->
<div class="bg-success/10 border border-success text-success rounded-lg p-4">
    <i class="fas fa-check-circle mr-2"></i>
    Success message
</div>

<!-- Warning Alert -->
<div class="bg-warning/10 border border-warning text-warning rounded-lg p-4">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    Warning message
</div>

<!-- Danger Alert -->
<div class="bg-danger/10 border border-danger text-danger rounded-lg p-4">
    <i class="fas fa-times-circle mr-2"></i>
    Error message
</div>

<!-- Badge -->
<span
    class="bg-primary text-white text-xs font-semibold px-2 py-1 rounded-full"
>
    New
</span>
```

### Card Styles

```html
<!-- Basic Card -->
<div
    class="bg-white rounded-xl shadow-sm hover:shadow-lg transition border border-gray-100 hover:border-primary/30 p-6"
>
    <h3 class="text-lg font-bold text-gray-900 mb-2">Card Title</h3>
    <p class="text-gray-600">Card content goes here</p>
</div>

<!-- Gradient Card -->
<div
    class="bg-gradient-to-br from-primary to-secondary text-white rounded-xl shadow-lg p-6"
>
    <h3 class="text-xl font-bold mb-2">Featured Card</h3>
    <p>Special content</p>
</div>
```

### Navigation Bar Styles

```html
<!-- Top Bar -->
<div class="bg-gradient-to-r from-primary to-secondary text-white">
    <div class="container mx-auto px-4 py-2">
        <!-- Content -->
    </div>
</div>

<!-- Navigation Item -->
<a
    href="#"
    class="text-gray-700 hover:text-primary font-medium py-2 border-b-2 border-transparent hover:border-primary transition"
>
    <i class="fas fa-home mr-2"></i>Home
</a>
```

## 📊 Theme Color Palette

### Primary (Blue)

-   **Default**: #3b82f6 (Blue 500)
-   **Light**: #60a5fa (Blue 400)
-   **Dark**: #2563eb (Blue 600)

Usage: Main actions, links, primary buttons

### Secondary (Violet)

-   **Default**: #8b5cf6 (Violet 500)
-   **Light**: #a78bfa (Violet 400)
-   **Dark**: #7c3aed (Violet 600)

Usage: Secondary actions, accents, gradients

### Success (Emerald)

-   **Default**: #10b981 (Emerald 500)

Usage: Success messages, positive actions, completed states

### Warning (Amber)

-   **Default**: #f59e0b (Amber 500)

Usage: Warning messages, caution indicators

### Danger (Red)

-   **Default**: #ef4444 (Red 500)

Usage: Error messages, destructive actions, delete buttons

## ⚡ Pro Tips

1. **Always use theme colors** instead of hardcoded values:

    ```html
    <!-- ❌ Bad -->
    <div class="bg-blue-500">Content</div>

    <!-- ✅ Good -->
    <div class="bg-primary">Content</div>
    ```

2. **Use hover states** for better UX:

    ```html
    <button class="bg-primary hover:bg-primary-dark transition">Button</button>
    ```

3. **Add transitions** for smooth animations:

    ```html
    <a class="text-gray-700 hover:text-primary transition"> Link </a>
    ```

4. **Use opacity** for subtle variations:

    ```html
    <div class="bg-primary/10">Light primary background</div>
    <div class="bg-primary/20">Medium primary background</div>
    ```

5. **Combine with Tailwind utilities**:
    ```html
    <button
        class="bg-primary hover:bg-primary-dark text-white font-bold py-3 px-6 rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all"
    >
        Enhanced Button
    </button>
    ```

## 🔄 Changing Theme Colors

Edit `config/theme.php`:

```php
'colors' => [
    'primary' => [
        'DEFAULT' => '#your-color',  // ← Change this
        'light' => '#lighter-shade',
        'dark' => '#darker-shade',
    ],
],
```

Then clear cache:

```bash
php artisan config:clear
```

## 📱 Responsive Design

Theme colors work with all breakpoints:

```html
<div class="bg-primary md:bg-secondary lg:bg-success">
    Responsive background
</div>

<p class="text-sm sm:text-base md:text-lg text-primary">Responsive text</p>
```

## 🎭 Dark Mode (Future Enhancement)

When implementing dark mode, you can extend theme:

```php
'colors' => [
    'primary' => [
        'DEFAULT' => '#3b82f6',
        'dark-mode' => '#60a5fa', // Lighter shade for dark mode
    ],
],
```

---

**Quick Reference Card**

| Element           | Class                                             |
| ----------------- | ------------------------------------------------- |
| Primary Button    | `bg-primary hover:bg-primary-dark text-white`     |
| Secondary Button  | `bg-secondary hover:bg-secondary-dark text-white` |
| Primary Link      | `text-primary hover:text-primary-dark`            |
| Success Badge     | `bg-success text-white`                           |
| Danger Badge      | `bg-danger text-white`                            |
| Input Focus       | `focus:border-primary focus:ring-primary`         |
| Gradient          | `bg-gradient-to-r from-primary to-secondary`      |
| Card Border Hover | `border-primary/30`                               |
