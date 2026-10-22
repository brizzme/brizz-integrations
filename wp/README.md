# wp/ — the "Brizz Tickets" WordPress plugin

Part of `brizz-integrations` (one repo for every Brizz-on-another-platform
piece); this folder is self-contained — run everything below from here.

Sell Brizz event tickets on an organiser's own WordPress site. A block and a
shortcode that print the Brizz checkout widget's `<div data-brizz-checkout>`,
load the widget script only where needed, and tell the organiser the exact
host to list on Brizz.

The plugin is the installable answer to a real support session (2026-10-22):
pasting two lines of HTML into a modern WordPress stack (Elementor's new
editor + a snippet plugin) took three screenshots, and the two failure
points — the site list is an exact host match; the only visible error is
"Tickets are currently unavailable" — were invisible. Background and the
widget's server side: `brizz_stack/docs/specs/BRIZZ_CHECKOUT_WIDGET_OPS_HANDOFF.md`
§8–§9.

## Layout

```
brizz-tickets/              the plugin (this folder is what ships)
  brizz-tickets.php         header, constants, boot
  includes/
    class-brizz-tickets-renderer.php   attributes → widget HTML (pure PHP, tested)
    class-brizz-tickets-plugin.php     shortcode, block registration, loader enqueue
    class-brizz-tickets-settings.php   Settings → Brizz Tickets
  blocks/tickets/           block.json + plain-JS editor (no build step)
  readme.txt                WordPress.org readme (external-services disclosure included)
  uninstall.php
tests/                      PHPUnit, no WordPress install needed (shims in bootstrap.php)
bin/build-zip.sh            → dist/brizz-tickets-<version>.zip
```

The widget contract the renderer implements lives in
`brizz-website-v2/public/widget/v1/checkout.js` — change both together.

## Develop

```bash
composer install            # phpunit only; no lockfile is committed
composer test
bin/build-zip.sh            # dist/brizz-tickets-1.0.0.zip
```

Manual check on a WordPress: upload the zip, activate, Settings → Brizz
Tickets, paste a snippet from brizz.me, add the block to a page, open the
published page. The cards only draw on the published page (the loader does
not run in the editor).

## Release

1. Bump `Version:` in `brizz-tickets/brizz-tickets.php`, `Stable tag:` and the changelog in `readme.txt`, and `version` in `blocks/tickets/block.json`.
2. `composer test && bin/build-zip.sh`.
3. Tag `wp-v<version>` (the folder prefix keeps this tag stream apart from other integrations), push, and attach the zip to a GitHub Release — the Brizz embed page links to the repo's releases page.
4. WordPress.org directory submission (one-click install + auto-updates for organisers) is the owner's call; `readme.txt` is written to pass review.
