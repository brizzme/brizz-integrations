=== Brizz Tickets ===
Contributors: brizzme
Tags: tickets, events, checkout, embed, brizz
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sell tickets for your Brizz events on your own WordPress site — a block and a shortcode that show your event cards and open the Brizz checkout.

== Description ==

[Brizz](https://brizz.me) is where Indians in Germany find and book events. If you organise events on Brizz, this plugin puts your tickets on your own site:

* **A block** — "Brizz Tickets" — and a **shortcode** `[brizz_tickets]`.
* Shows **all your upcoming events** as cards (new events appear by themselves), **one event** as a card or a "Get tickets" button, or a hand-picked set.
* Tapping a card opens the **Brizz checkout on top of your page**. Payments run through Brizz and Stripe; every sale shows in your Brizz dashboard.
* Loads the Brizz widget script only on pages that use it, and tells you the exact host to list on Brizz so the widget is allowed to load.

No account data is stored in WordPress — the plugin keeps one setting (which organiser or event to show by default).

Full guide, with the two-line snippet for sites that are not WordPress: https://brizz.me/host-an-event/your-website

== Installation ==

1. Download `brizz-tickets-<version>.zip` from the newest "wp-v…" release at https://github.com/brizzme/brizz-integrations/releases (or install from the WordPress.org directory when listed). Leave it zipped.
2. In WordPress admin: **Plugins → Add New → Upload Plugin → Choose File** → the zip → **Install Now**, then **Activate Plugin**. "Brizz Tickets" appears under Settings.
3. On brizz.me, signed in as the organiser: **My Events → Embed on your site**. Under "Tell us where it will appear", add your site's domain — **Settings → Brizz Tickets** in WordPress shows the exact host(s) to add (`www.example.com` and `example.com` are two different entries). Then copy the snippet.
4. In WordPress: **Settings → Brizz Tickets**, paste the snippet, **Save Changes**. The page now says "All your upcoming events" (or "One event"). The plugin keeps only the id inside the snippet.
5. Edit the page where the tickets should appear and add the **Brizz Tickets** block — or `[brizz_tickets]` in any editor (Elementor's Shortcode widget, Divi, WPBakery, the classic editor). Update.
6. Open the published page. Your event cards appear; tapping one opens the Brizz checkout on top of the page. In the editor you only see a placeholder — that is expected.
7. If it says "Tickets are currently unavailable", the domain is not listed yet: the Embed page's "Seen on your sites" names it — click Allow and reload.

== Frequently Asked Questions ==

= The page says "Tickets are currently unavailable". =

The widget only loads on sites listed on Brizz, and the list matches the exact host — `www.example.com` and `example.com` are two different entries. Settings → Brizz Tickets shows the host(s) your site answers on; add them under Embed on your site at brizz.me. That page also lists the sites the widget was tried from, with an Allow button.

= Nothing appears in the editor. =

That is expected. The cards are drawn by the Brizz widget script, which runs on the published page, not inside the editor. The block shows a placeholder saying what it will show.

= Can I show just one event? =

Yes: set the block's source to "One event", or use `[brizz_tickets event="…"]` with the event's id from its Embed page on brizz.me. Add `mode="button"` for a "Get tickets" button instead of a card.

= Does this work with Elementor, Divi or WPBakery? =

Yes — use the shortcode in their Shortcode / Code module. The block works in the WordPress block editor.

= Where do payments go? =

To Brizz, through Stripe, exactly as for tickets sold on brizz.me. Your payout and fees are shown in your Brizz dashboard. The plugin itself handles no money and stores no buyer data.

== External services ==

This plugin loads the Brizz checkout widget script from `https://brizz.me/widget/v1/checkout.js` on pages where a Brizz Tickets block or shortcode is used. The script runs in the visitor's browser, connects to `https://api.brizz.me` to load your event cards and start a checkout, and opens the checkout from `https://brizz.me` in an overlay. The visitor's browser therefore sends requests to brizz.me and api.brizz.me, as it would on any site embedding Brizz; the page's address is sent so Brizz can confirm the site is on your allowed list. Brizz's terms and privacy policy apply: https://brizz.me/terms and https://brizz.me/privacy

== Changelog ==

= 1.0.0 =
* First release: block, shortcode, settings page with snippet paste and the exact host to list on Brizz.
