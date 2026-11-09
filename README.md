# Webinew Related Products

**Save-time product recommendations for WordPress articles and WooCommerce.**

Developed by **احسان قسیمی (Ehsan Ghasimi)** at **[شرکت وبینیو — Webinew](https://webinew.com/)**.

وبینیو در زمینه طراحی سایت، توسعه وردپرس، سئو و بهینه‌سازی وب‌سایت فعالیت می‌کند. این افزونه یکی از ابزارهای متن‌باز وبینیو برای فروشگاه‌های ووکامرسی است. برای توسعه اختصاصی و خدمات وب، به [وبینیو](https://webinew.com/) مراجعه کنید.

## Features

- Up to seven automatic recommendations based on article title/content and product names, categories, tags and short descriptions.
- Persian/Arabic letter normalization and weighted keyword matching. No external AI service or API key.
- Recommendations computed on article save; no catalog scan on public page views.
- Editorial meta box: manual IDs, ordering, removal, and refresh of automatic suggestions. Up to 20 manual products.
- Product image, name, current WooCommerce price, and purchase link.
- Lightweight RTL sidebar slider with five-second autoplay, pause/play, hover/focus pause, keyboard controls and reduced-motion support.
- No third-party scripts, tracking, injected public promotional links, or external recommendation requests. Webinew attribution appears in plugin metadata, the editor meta box and documentation.

## Install

Requires WordPress 6.2+, PHP 7.4+ and active WooCommerce. Use a currently supported PHP release in production.

1. Download `webinew-related-products-1.1.0.zip` from [Releases](https://github.com/EhsanGhasimi/webinew-related-products/releases).
2. Upload the ZIP in **Plugins → Add New → Upload Plugin**, then activate it.
3. Save an article to generate recommendations; reload the editor to see the updated IDs.
4. Place a Shortcode widget/block in the article sidebar, or Elementor's Single Post template:

```text
[webinew_related_products]
```

Custom title:

```text
[webinew_related_products title="محصولات مرتبط با این مقاله"]
```

The plugin does not create a sidebar or automatically replace an existing product widget. Remove the old widget yourself if both appear.

## Upgrade from the initial project-specific package

Deactivate the earlier plugin **before activating this plugin**, since it registers the same compatibility shortcode. Install this package as a new plugin; its folder/file name changed. Saved product selections remain available: legacy `_bzap_` post-meta keys are intentionally preserved. The previous shortcode `behzist_related_products` also continues to work. New installations should use `webinew_related_products`.

Clear page, asset optimization and browser caches after updating. Do not keep both plugin versions active.

## Editorial workflow

The editor box **محصولات مرتبط مقاله — وبینیو** displays IDs and product names. In automatic mode, changing title/content/excerpt refreshes suggestions on save. An unchanged save skips the scan.

Select manual mode to edit the comma-separated ID list; order is preserved and Persian digits are supported. Manual selections survive later content edits. An empty manual list hides the component. The rescan checkbox refreshes suggestions separately, preserving the manual selection. Switch back to automatic mode to use the saved suggestions.

Hidden, unpublished, deleted and out-of-stock products are omitted when rendering. Simple purchasable products link to add-to-cart; other types open their product page. Missing prices show a contact-for-price message.

## Performance and limits

This is **keyword matching, not semantic AI**. Review suggestions before publication. Insufficient matches produce fewer than seven results rather than unrelated fallback products.

Save-time scanning synchronously reads the catalog in batches of 100 and retains token data in memory. Large catalogs may slow article saves and require a persistent index/background queue; this version is intended for smaller and medium catalogs. Page views read only saved IDs and current WooCommerce product data, so they still involve normal product reads. Full-page caches may show stale prices until purged.

Product edits do not automatically rebuild every article's recommendations. Rescan the article after adding or substantially changing products. Only normal article title/content/excerpt are read; content held exclusively in custom fields or dynamic templates is not scanned.

## Development checks

```sh
php -n tests/test.php
node tests/test-slider.cjs
php -n -l webinew-related-products.php
php -n -l matcher.php
node --check assets/slider.js
```

The PHP tests use WordPress/WooCommerce stubs to verify matching, scan skipping, manual preservation, ID parsing and nonce rejection. JavaScript tests verify autoplay and interaction states. These are not full WordPress integration tests. Test your actual theme, WooCommerce version, cart behavior, page builder and caches on staging before production use.

## License and credits

GPL-2.0-or-later. Copyright © 2026 احسان قسیمی and شرکت وبینیو.

**[Webinew](https://webinew.com/) — طراحی سایت، توسعه وردپرس و سئو.**

راهنمای فارسی: [راهنما.md](راهنما.md)
