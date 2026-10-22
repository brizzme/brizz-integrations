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

1. Upload the plugin and activate it.
2. On brizz.me, open **My Events → Embed on your site**. Add your site's domain to the site list (the plugin's settings page shows you the exact host to add), and copy the snippet.
3. In WordPress, open **Settings → Brizz Tickets** and paste the snippet. The plugin keeps only the id inside it.
4. Add the **Brizz Tickets** block where the tickets should appear — or the shortcode `[brizz_tickets]` in any editor.
5. Open the published page. You should see your event cards; tapping one opens the checkout.

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
