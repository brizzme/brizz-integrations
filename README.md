# brizz-integrations — Brizz on other people's platforms

One repo for every piece of Brizz that runs inside someone else's product:
plugins, apps and embeds that put Brizz events, tickets and the checkout on
an organiser's own site. Each integration is a folder with its own tooling,
tests and release tags; nothing here deploys to a Brizz server.

| Folder | What | Status | Release tag |
|---|---|---|---|
| [`wp/`](wp/) | **Brizz Tickets** WordPress plugin — block + `[brizz_tickets]` shortcode over the checkout widget, settings that read the pasted snippet and name the exact host to list on Brizz | v1.0.0 built 2026-10-22 | `wp-v<version>`; the zip is the release asset |

Planned, not started: Shopify (Custom Liquid block → app), Wix, Squarespace.
The embed itself — the two-line snippet every platform can paste — is in
`brizz-website-v2` (`public/widget/v1/checkout.js`); the integrations only
print its element and load that script. Change the contract in both places.

## Conventions

- **Branch:** `main` only. Releases are tags prefixed with the folder name
  (`wp-v1.0.0`), one tag stream per integration, so a WordPress
  release never looks like a Shopify one.
- **No lockfiles** (`brizz_stack/CLAUDE.md`, "Lockfiles are NOT committed").
- Tests run without the host platform installed wherever possible (the
  WordPress folder shims the handful of WP functions it touches).
- Background, decisions and the server side of the widget:
  `brizz_stack/docs/specs/BRIZZ_CHECKOUT_WIDGET_OPS_HANDOFF.md` §8–§9.

## Release (WordPress)

```bash
cd wp && composer install && composer test && bin/build-zip.sh
git tag -a wp-v1.0.0 -m "Brizz Tickets 1.0.0" && git push origin main --tags
```

Then attach `wp/dist/brizz-tickets-<version>.zip` to the GitHub
Release for that tag. The Brizz embed page links to this repo's releases.
