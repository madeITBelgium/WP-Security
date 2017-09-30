=== Forms ===
Contributors: madeit
Donate link: http://www.madeit.be/donate/
Tags: security, maintenance, secure, security plugin, wordpress security, maintenance plugin
Requires at least: 4.0
Tested up to: 4.8.2
Requires PHP: 5.6
Stable tag: 1.0
License: GNU GPL v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Secure your WordPress Website.

== Description ==
'WP Security by Made I.T.' is one of the fastest WordPress security plugins. We daily scan your complete website to check mallware, virusses and files changes.

This plugin automaticly generates a connection with our servers to generate an API key and update scan listings, mallware and virus databases, ...

= Features =
* Scan your WordPress website to file changes.
* Compare the canged files with its original.
* Backup you Website.
* Made I.T. WordPress Maintenance integration.
* Security Alerts


= Comming Features =
* Check for mallware and virusses.
* Login prevention
* Firewall


== Installation ==

1. Upload the entire `wp-security-by-made-it` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

You will find 'Security & Maintenance' menu in your WordPress admin panel.

For basic usage, you can also have a look at the [plugin homepage](https://www.madeit.be/wordpress-onderhoud).

== Frequently Asked Questions == 

= Wich data is send to the Made I.T. servers? =
In the initial activation of te plugin we send the following data to our server to generate an API key to communicate with our servers to download features scan listings, mallware and virus databases.
Data we collect: php version, mysql version, WordPress version, URL, user count, site count, OS name and version

= What is Made I.T. Maintenance =
Made I.T. Maintenance is a paid service. To help you to focus on your real work we do everything for your website. We check updates, security, ... We improve website speed and help you with problems.

= What is fast scanning? =
When Plugin, Theme or Core scanning is enabled we first do a fast scan. This fast scan generates a hash of each plugin, theme and the WordPress core. The result of this hash is send to our server to check with the hash we have generated. If there is a mismatch we know that there are changed files. Here stops the fast scan. When fast scanning is disabled we check every failed plugin, theme or WordPress core to check wich file is changed and how.


== Changelog ==

= 0.1 =
* Check core, plugins and themes against repository for changes.
* Notify updates to Made I.T.
* Do daily backup