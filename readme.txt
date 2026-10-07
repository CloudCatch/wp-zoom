=== WP Events for Zoom ===
Contributors: cloudcatch, dkjensen
Tags: zoom,webinars,meetings,woocommerce
Requires at least: 5.4
Tested up to: 7.1
Requires PHP: 7.0.0
Stable tag: 0.0.0-development
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Sell, display, register users for webinars with WP Events for Zoom

== Description ==
WP Events for Zoom has a native WooCommerce integration, allowing you to sell webinars and register users automatically when completing checkout.

This plugin integrates with the Zoom API located at https://api.zoom.us/v2


== Installation ==

= Minimum Requirements =

* PHP 7.0 or greater is required
* MySQL 5.6 or greater is recommended

1. Install and activate the plugin.
2. In WordPress, open **Settings → WP Events for Zoom**.
3. Click the Zoom button and approve access. The site connects through the CloudCatch connector at `https://oauth.cloudcatch.io`. You do not need to create a Zoom app for this path.

= Optional: use your own Zoom app =

Define `WP_ZOOM_CLIENT_ID` and `WP_ZOOM_CLIENT_SECRET` in **wp-config.php** to skip the CloudCatch connector and authorize against a Zoom app you control.

1. Visit [Zoom Marketplace](https://marketplace.zoom.us/develop/create) and create a user-managed OAuth app.
2. Set the redirect URL and the allow list to your site's `/wp-admin/options-general.php?page=wp-zoom`.
3. Add these scopes: **user:read**, **webinar:read**, **webinar:write**, **meeting:read**.
4. Put the app's client id and secret in the constants above.
5. Open **Settings → WP Events for Zoom** and authorize.

== External services ==

This plugin connects to Zoom so a site administrator can authorize an account and sell, list, and register people for Zoom webinars.

Zoom is used when an administrator connects the site, when the plugin refreshes an expired access token, and when it loads webinars or creates a registrant. The plugin sends the OAuth authorization code or refresh token, and the Zoom user id, webinar id, and registrant details (such as name and email collected at checkout) to `https://zoom.us` and `https://api.zoom.us`. Webinar details returned by Zoom are shown on the site.

This service is provided by Zoom: [terms of service](https://www.zoom.com/en/trust/terms/), [privacy statement](https://www.zoom.com/en/trust/privacy/privacy-statement/).

If `WP_ZOOM_CLIENT_ID` and `WP_ZOOM_CLIENT_SECRET` are not defined, the plugin uses the CloudCatch Zoom connector at `https://oauth.cloudcatch.io` instead of a Zoom app created for this site. That connector is only used when an administrator clicks the authorize button. The administrator is redirected there to approve Zoom access, and the connector returns OAuth tokens to this site. Define your own Zoom app credentials in `wp-config.php` to connect to Zoom directly and skip this connector.

This service is provided by CloudCatch: [terms of use](https://cloudcatch.io/terms), [privacy policy](https://cloudcatch.io/privacy).

== Changelog ==

= 1.6.0 =
* Tested with WordPress 7.1 and declared the GPL-3.0 license.
* Connect through oauth.cloudcatch.io when the site does not define its own Zoom app credentials.
* Require the administrator who started authorization to finish it, and revoke the Zoom token on disconnect.
* Remove the bundled Composer libraries.
* Add settings for when a customer is registered, which billing fields are sent, and optional API logging.
* Send street address, city, state, postal code, country, phone, and company only when those settings are turned on. Email and name are still always sent.
* Treat a Zoom account without a webinar plan as an empty list.
* Load the calendar script only with the calendar shortcode, and remove the jQuery dependency from the product editor.
* Delete stored tokens, settings, and logs when the plugin is deleted.
