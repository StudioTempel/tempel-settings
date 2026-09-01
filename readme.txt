=== Tempel settings ===
Contributors: studiotempel
Tags: tempel, admin, widgets, gravity forms, performance
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 7.1
Stable tag: 2.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Studio Tempel helper plugin voor custom-built WordPress websites.

== Description ==

Tempel settings bundelt een aantal Studio Tempel helpers voor WordPress websites, waaronder admin-branding, dashboard-widgets, Gravity Forms adresvelden, mailfuncties, SVG-ondersteuning, content dupliceren, taxonomy ordering en performance-instellingen.

== Installation ==

1. Upload de pluginmap `tempel-settings` naar `/wp-content/plugins/`.
2. Activeer de plugin via het WordPress plugin-overzicht.
3. Configureer de gewenste onderdelen via `Tempel Settings`.

== Changelog ==

= 2.8.0 =
* Voeg beveiligingsblokkades toe voor plugininstallaties, nieuwe beheerders en de ingebouwde bestandseditor.
* Deactiveer kwetsbare WPMU DEV Dashboard-versies tot en met 5.0.1 automatisch wanneer deze actief zijn.
* Stuur meldingen bij plugininstallaties, nieuwe beheerders en promoties naar beheerder.
* Voeg optionele tweestapslogin via een zescijferige e-mailcode toe voor backendgebruikers zonder Defender 2FA.
* Voeg een instelbare Gravity Forms-bewaartermijn, handmatige opschoning en een begrensd conversiedashboard toe.
* Bewaar inzendingen op exact de ingestelde grensdatum en verwijder uitsluitend oudere inzendingen.
* Bundel adresinstellingen, bewaartermijn en onzichtbare antispam op één Gravity Forms-pagina.
* Herstel de resultaatopbouw van het conversiedashboard voor meerdere geselecteerde formulieren.

= 2.7.43 =
* Bundel adresveld, antispam en bewaartermijn op één Gravity Forms-instellingenpagina.
* Voeg optionele globale onzichtbare antispam toe met honeypot, JavaScript-controle en minimale invultijd.
* Verdachte inzendingen worden als spam gemarkeerd in plaats van zichtbaar geblokkeerd.

= 2.7.42 =
* Voeg een handmatige knop toe om direct maximaal 500 oude Gravity Forms-inzendingen op te schonen.
* Voer de uurlijkse opschoning ook veilig uit bij beheerbezoek wanneer WP-Cron niet betrouwbaar draait.
* Herstel het inplannen van de opruimtaak wanneer Tempel Settings tijdens WordPress init wordt geladen.

= 2.7.41 =
* Fix: voorkom een redirectlus wanneer de codestap leeg of automatisch wordt verstuurd.

= 2.7.40 =
* Toon de login-code in de e-mail groot en op een eigen regel.

= 2.7.39 =
* Fix: een lege of automatisch verstuurde codestap toont geen onjuiste-codefout meer en kost geen loginpoging.

= 2.7.38 =
* Fix: dwingt de e-mailcode ook af wanneer Defender of een andere loginprovider de gebruiker buiten de standaard WordPress-wachtwoordcontrole authenticeert.

= 2.7.37 =
* Only bypass Tempel email verification when the active Defender runtime confirms an actually available 2FA provider for the user.

= 2.7.36 =
* Redirect to the dedicated code step after a valid password so Defender no longer counts requesting a code as a failed login attempt.

= 2.7.35 =
* Changed email verification to a true two-step login: credentials first, then a dedicated code screen with a reset link.

= 2.7.34 =
* Fixed the email-code input being hidden by the branded login screen and clarified the second login step.

= 2.7.33 =
* Send info@studiotempel.nl an email when an administrator is created or an existing user is promoted to administrator.

= 2.7.32 =
* Fixed email login verification for masked WordPress login URLs such as Defender's custom login route.

= 2.7.31 =
* Added optional six-digit email verification for direct WordPress logins when the user has no configured Defender 2FA provider.
* Excluded WooCommerce frontend logins and other non-WordPress login flows from the fallback verification.

= 2.7.30 =
* Send info@studiotempel.nl an email after a plugin is installed through WordPress.
* Added an optional global retention period and automatic permanent cleanup for Gravity Forms entries.
* Limit the conversion dashboard period to the configured retention period when it is shorter than 30 days.

= 2.7.29 =
* Automatically deactivate WPMU DEV Dashboard 5.0.1 and older once when active.
* Disable the built-in plugin and theme file editor while the security lock is enabled.

= 2.7.28 =
* Enable the security lock by default on installation and once on upgrade. Subsequent manual changes are preserved.

= 2.7.27 =
* Added an optional security lock to block plugin installations and new administrator assignments.

= 2.7.26 =
* Added a compact website status overview for WordPress, PHP, memory, cron, mail, API, HTTPS and search visibility.
* Added a privacy-conscious technical log for mail, PostcodeAPI and Gravity Forms results with filters and automatic 30-day cleanup.
* Added twice-daily health checks and safe HTTPS webhook endpoint monitoring.

= 2.7.25 =
* Enabled Tempel content duplication by default and deactivated Yoast Duplicate Post once the replacement is active.
* Added secure user switching with a return action and deactivated the separate User Switching plugin once the replacement is active.
* Hid the WordPress 7.1 site icon in the custom admin toolbar while keeping the site menu available.

= 2.7.24 =
* Validate Dutch postcode formatting during Gravity Forms submission before data is sent to external APIs.

= 2.7.23 =
* Renamed the final settings tab to Info and combined plugin status with the changelog.
* Removed the separate Status tab.

= 2.7.22 =
* Added a dedicated Changelog page to Tempel Settings.
* Consolidated the reconstructed release history in `CHANGELOG.md`.

= 2.7.21 =
* Prepared and validated the production release package.

= 2.7.20 =
* Aligned the classic menu copy action with the save button on the right side.

= 2.7.19 =
* Added bulk duplication for supported posts, custom post types and taxonomy terms.
* Improved the classic menu action layout and delete icon.
* Fixed the menu duplication nonce URL.

= 2.7.18 =
* Added optional duplication actions for posts, pages, custom post types and taxonomy terms.
* Added menu duplication with preserved item hierarchy and without assigning menu locations.

= 2.7.17 =
* Ensured login error messages from security plugins remain white on the dark login screen.

= 2.7.16 =
* Disabled bundled WordPress themes by default for existing installations.

= 2.7.15 =
* Removed the service contract settings and status widget output.

= 2.7.14 =
* Made login error messages and Defender attempt notices white on the dark login screen.

= 2.7.13 =
* Fixed the masked-login security message line break when the message is escaped before rendering.

= 2.7.12 =
* Changed the masked-login security message line break to HTML so it displays correctly.

= 2.7.11 =
* Added the StudioTempel team sign-off to the masked-login security message.

= 2.7.10 =
* Updated the WP Defender masked-login security message text.

= 2.7.9 =
* Replaced the WP Defender masked-login security message with a clearer Dutch message.

= 2.7.8 =
* Added extra spacing between the email address and password fields on the login screen.

= 2.7.7 =
* Updated the login screen to a dark background and card style with a yellow full-width login button.

= 2.7.6 =
* Updated the login screen button to full-width black styling and centered the lost password link below it.

= 2.7.5 =
* Added default Mail subject and message content for the WordPress admin URL notification.

= 2.7.4 =
* Updated the login screen styling: removed the background photo, centered the login form, switched to the yellow brand background and hid the privacy policy link.

= 2.7.3 =
* Preserve selected recipients, subject and WYSIWYG message content after sending a mail.

= 2.7.2 =
* Moved the mail placeholder tags to the top of the Mail settings page.

= 2.7.1 =
* Added mail tags for personalized user emails, including `[naam]`, `[voornaam]`, `[email]`, `[website_url]` and `[website_naam]`.
* Moved the Mail settings tab to the end of the Tempel Settings navigation.

= 2.7.0 =
* Added a Mail settings page for sending HTML email from the website to selected WordPress users.
* Added a WYSIWYG editor, user multi-select, permission checks and nonce-protected sending via `wp_mail()`.

= 2.6.10 =
* Show the saved PostcodeAPI.nu API key in the password field again.
* Added a masked API-key preview below the API-key label.

= 2.6.9 =
* Changed the untested PostcodeAPI status from warning yellow to neutral grey.
* Allowed the API test button to use a newly typed API key and endpoint before saving.

= 2.6.8 =
* Simplified the Status tab by removing duplicate helper text and letting status items use the full content width.

= 2.6.7 =
* Fixed the Status settings tab registration so WordPress loads the page with the correct permissions.

= 2.6.6 =
* Moved the plugin health/status block to a separate Status settings tab.
* Placed Status as the first settings tab.

= 2.6.5 =
* Added a Gform address field health/status block for Gravity Forms, API key, PostcodeAPI, cache and performance status.
* Added a PostcodeAPI.nu connection test button with example postcode and house number.

= 2.6.4 =
* Improved settings input, password, select and Select2 styling.
* Made settings controls wider and vertically centered for a more consistent layout.
* Improved dropdown arrow visibility.

= 2.6.3 =
* Set the Gform address field feature to disabled by default on install and update.

= 2.6.2 =
* Added this WordPress `readme.txt` so the plugin details modal can show plugin information and changelog entries.

= 2.6.1 =
* Improved dropdown and Select2 styling on the Widgets settings page.
* Renamed the Widget settings tab to Widgets.
* Kept the Gform address API-key field empty in the UI by default.
* Preserved an existing PostcodeAPI.nu API key when the API-key field is left empty while saving.
* Confirmed the default PostcodeAPI.nu endpoint uses the live endpoint: `https://api.postcodeapi.nu/v3/lookup`.

= 2.6.0 =
* Removed the Magic Login module and settings.
* Moved Performance settings to a separate settings page.
* Added Performance presets for normal sites, webshops and heavy websites.

= 2.5.35 =
* Reduced spacing between the Gform address field and Gravity Forms validation messages.
* Hid the empty internal address lookup message so it no longer reserves visual space.

= 2.5.34 =
* Made the address addition field optional for Gravity Forms required-field validation.
* Prevented the optional addition field from being marked as required in address field markup.

= 2.5.33 =
* Refined required-state handling for the Gform address subfields.
* Kept required attributes limited to postcode and house number.

= 2.5.32 =
* Improved Dutch frontend messages for the Gform address field.
* Added/updated the separate Gform address field settings page styling.
* Added safer handling around PostcodeAPI.nu messages and connection failures.

= 2.5.31 =
* Improved "address not found" messaging for the PostcodeAPI.nu lookup.

= 2.5.30 =
* Improved short and human-readable validation messages for incomplete Dutch postcodes.

= 2.5.29 =
* Refined Gform address field layout and read-only styling.

= 2.5.28 =
* Added the separate Gform address field settings page.
* Added PostcodeAPI.nu usage limit settings, cache settings and request-rate controls.
* Added API usage notices for high usage and exhausted limits.

= 2.5.24 - 2.5.27 =
* Iterated on PostcodeAPI.nu connection handling and frontend address lookup behavior.
* Improved request deduplication and error handling for address lookups.

= 2.5.18 - 2.5.23 =
* Improved Gform address field layout, placeholders and manual-input behavior.
* Added controls for hiding labels and customizing address field messages.

= 2.5.9 - 2.5.17 =
* Added and refined the Gravity Forms Dutch address lookup field.
* Added PostcodeAPI.nu based address lookup with caching and frontend updates.

= 2.5.8 =
* Added performance measures and settings for memory limits, revisions, Heartbeat, emojis, embeds and XML-RPC.

= 2.5.7 =
* Version bump and maintenance update.

= 2.5.6 =
* Version bump and maintenance update.

= 2.5.4 =
* Version bump and maintenance update.

= 2.5.1 =
* Version bump and maintenance update.

= 2.5.0 =
* Created the 2.5.0 plugin package.

= 2.4.2 =
* Updated plugin metadata/version handling.

= 2.4.0 =
* Initial 2.4 release line.

= 2.1.4 =
* Integrated the new admin settings page styling.

= 2.1.3 =
* Version bump for the Cloudways-related release line.

= 2.1.2 =
* Version bump.

= 2.1.1 =
* Fixed a case-sensitive require issue.

== Upgrade Notice ==

= 2.7.15 =
Removes the service contract settings and status widget output.
