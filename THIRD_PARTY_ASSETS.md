# Third-Party Assets

This repository ships application code under the MIT license, but it also includes third-party frontend assets, fonts, and CDN references that keep their own licenses and usage terms.

## Bundled frontend libraries

The admin UI currently includes locally vendored assets under `public/assets/**`, including these major libraries:

- AdminLTE 3.1.0
- Bootstrap 4.1.3
- jQuery 3.3.1
- DataTables
- Chart.js
- Select2
- Summernote
- Toastr
- overlayScrollbars
- jqvmap
- Font Awesome Free

Where a bundled package already includes its own `LICENSE`, `README`, or header notice, that upstream license remains the controlling license for that asset.

## CDN-delivered assets

The Blade layouts also reference third-party CDNs for some assets, including:

- Ionicons
- SweetAlert
- Google Fonts

The admin, customer, home, and auth layouts now rely on the bundled local Font Awesome asset instead of loading separate CDN copies.

The customer, home, and auth layouts now load Tailwind and Alpine from the existing Vite/npm pipeline instead of CDN scripts.

Toastr and jQuery UI are now served from bundled local assets instead of CDN URLs in the migrated layouts.

If you distribute this project commercially, review those hosted assets before release and either:

1. keep the CDN usage and document it in your product package, or
2. pin and vendor approved copies with their license notices.

## Font Awesome usage note

This project uses Font Awesome Free assets and brand icons in payment-related UI.

- Font Awesome Free code is MIT-licensed.
- Font files are licensed under SIL OFL 1.1.
- Icons are licensed under CC BY 4.0.
- Brand icons are subject to trademark restrictions from the respective brands.

Before commercial distribution, verify that any payment-brand icon usage matches the applicable brand guidelines.

## Kalpurush font provenance

The unlicensed Kalpurush font stylesheets were removed from the repository's active asset paths during the audit cleanup.

## Recommended release checklist

1. Keep only assets that are actually used in production.
2. Remove demo/sample assets from production layouts.
3. Store upstream license files beside manually vendored packages whenever possible.
4. Track any exceptions or unverifiable assets in this file before shipping.
5. Prefer one source per library in each layout to avoid duplicate CDN and local bundles.
