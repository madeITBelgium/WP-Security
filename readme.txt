=== WP Security By Made I.T. ===
Contributors: madeit
Donate link: http://www.madeit.be/donate/
Tags: security, maintenance, secure, security plugin, wordpress security, maintenance plugin
Requires at least: 4.0
Tested up to: 5.3
Requires PHP: 7.0
Stable tag: 1.8.1
License: GNU GPL v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Secure your WordPress Website.

== Description ==
'WP Security by Made I.T.' is one of the fastest WordPress security plugins. The plugin daily scan your complete website to check for malware, viruses and files changes.

This plugin relies on a third party service to functionally work. This plugin automatically generates a connection with our server on 'www.madeit.be' to create an API key to update scan listings, malware and virus databases. For more info, you can also have a look at our [plugin homepage].(https://madeit.be/wordpress-onderhoud/wp-plugin).

= Features =
* Scan your WordPress website to file changes.
* Compare the changed files with its original.
* Backup your Website.
* Made I.T. WordPress Maintenance integration.
* Security Alerts
* Vulnerability scanning thanks to wpvulndb.com
* Firewall (Experimental)
* Login prevention (Experimental)


= Comming Features =
* Check for malware and viruses. [1.8]

== Installation ==

1. Upload the entire `wp-security-by-made-it` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

You will find 'Security & Maintenance' menu in your WordPress admin panel.

For basic usage, you can also have a look at the [plugin homepage](https://www.madeit.be/wordpress-onderhoud).

== Frequently Asked Questions == 

= Wich data is sent to the Made I.T. servers? =
In the initial activation of the plugin, we send the following data to our server to generate an API key to communicate with our servers to download features scan listings, malware and virus databases.
Data we collect: PHP version, MySQL version, WordPress version, URL, user count, site count, OS name and version.
When scanning your website, we sent the plugin name and a hash of the plugin files to our server.

= What is Made I.T. Maintenance =
Made I.T. Maintenance is a paid service. To help you to focus on your real job, we maintain your website. We check updates, security, ... We improve website speed and help you with problems.

= What is fast scanning? =
When Plugin, Theme or Core scanning is enabled we first do a quick scan. This quick scan generates a hash of each plugin, theme and the WordPress core. The result of this hash is sent to our server to check with the hash we have generated. If there is a mismatch, we know that there are changed files. Here stops the fast scan. When fast scanning is disabled, we check every failed plugin, theme or WordPress core to check which file is changed and how.


== Changelog ==
= 1.8.1 =
* Fixed bug in firewall
* Fixed bug in weekly report

= 1.8.0 =
* Fix bug in saving settings
* Fix bug in enabling firewall
* Enable default settings
* Added weekly report

= 1.7.3 =
* Improve scanning performance

= 1.7.2 =
* Fix scan bug

= 1.7 =
* Firewall
* Login prevention

= 1.6.1 =
* Fix bug in reschedule of update scanner
* Improved loading files
* Fix bug that files look equal but diff tool show differences
* Add debug info for security jobs
* Fixed bug that many files are flagged as changed
* and more


= 1.6 =
* Better issue management
* Fix bug in restore backup
* Fix hanging cronjobs 

= 1.5 =
* VulnDB integration
* Delete cronjobs
* Backup CLI POC
* Issue management

= 1.4 =
* Rescan all files before taking backup
* Added a function to update all plugins, themes and core at once.
* Restore backup with changing domain name
* Fix bug that themes updates aren't notified
* Fix bug that class Diff isn't found on some installations

= 1.3.3 =
* Fix error when a website has many files.

= 1.3.2 =
* Bug fix in scanning
* NL Translation fixes

= 1.3.1 =
* Bug fix
* System info page

= 1.3 =
* Improve scanning on less performant servers
* Made I.T. Maintenance - Disk and memory usage.

= 1.2.2 = 
* Fix apache version search

= 1.2.1 =
* Fix save API key

= 1.2 =
* Upload backups to Made I.T., FTP or S3

= 1.1 =
* Replace file with original version
* Bug fixes

= 1.0 =
* Check core, plugins and themes against repository for changes.
* Notify updates to Made I.T.
* Do daily backup
