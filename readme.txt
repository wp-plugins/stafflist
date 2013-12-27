=== StaffList ===
Contributors: era404
Requires at least: 3.2.1
Tested up to: 3.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A super simplified staff directory tool (beta)

== Description ==

A very light-weight plugin, designed to easily create and manage a staff directory on your WordPress theme. Backend management for add/edit/remove staff records, and front end table with sort by Last Name, First Name, or Directory. Table is paginated and searchable.


**Simple, Straight-Forward**

This plugin doesn't bother with custom columns, initial-sort, skinning parameters. It just does what it needs to do.

* Update five fields for each staff record (last name, first name, department, email, phone number)
* Leave some fields blank for general department mailboxes or numbers
* Updates are performed on-the-fly, so no lengthy reloads are necessary.
* Design is split into separate stylesheet for ease of theming
* No edit links or popups, just make your changes in-line.
* Case-insensitive substring search, with highlighted matches on front end
* Uses jQuery/AJAX for page handling, sorting & searching without pageload

== Installation ==

1. Install StaffList either via the WordPress.org plugin directory, or by uploading the files to your server (in the `/wp-content/plugins/` directory).
1. Activate the plugin.
1. Create a staff record (or as many as you need)
1. Insert the staff directory into your page template with &lt;?php new stafflist(); ?&gt;

== Screenshots ==

1. The frontend staff directory
2. The backend directory manager
3. Example of a directory search

== Frequently Asked Questions ==

= Are there any new features planned? =
Yes. We plan to add a feature to use infinite scroll instead of page numbers.

= Can i propose a feature? =
If you wish. Sure.

== Changelog ==
= 0.97 =
* Updated links.

= 0.96 =
* Fixed issue with users pressing enter on a realtime search.

= 0.95 =
* Adjusted pager to allow for user config of # records/page.
* define('RECORDS_PER_PAGE', 25); //stafflist.php

= 0.94 =
* Improved styles.

= 0.93 =
* Improved styles.

= 0.92 =
* Added a banner image.

= 0.91 =
* Added a new screenshot to show the regex search results

= 0.9 =
* Plugin-out only in beta, currently. Standby for official release.