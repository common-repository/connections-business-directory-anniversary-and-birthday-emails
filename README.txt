=== Connections Business Directory Anniversary and Birthday Emails ===
Contributors: shazahm1@hotmail.com
Donate link: http://connections-pro.com/
Tags: business directory, anniversary, birthday, email
Requires at least: 5.1
Tested up to: 5.7
Requires PHP: 5.6.20
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An extension for the Connections Business Directory plugin which adds the ability to automatically send an email to entries on their anniversary or birthday.

== Description ==

This is an extension plugin for the [Connections Business Directory plugin](http://wordpress.org/plugins/connections/) please be sure to install and active it before adding this plugin.

Automatically send email congratulating individuals in you directory on their anniversary or birthday.

= Features =

* Choose which type of event emails to send. You can choose to enable support for either Anniversaries or Birthdays or both.
* Optional admin notification when event emails are sent.
* The admin notification email is completely configurable.
* The email for the anniversary and birthday are separately configurable allowing you create a custom email based on the event.
* Includes advanced features such as:
  * Define the number of days ahead of the event to sent the email.
  * The time of day the email should be sent.
  * How many emails to send per batch to help prevent going over you web host email send limits.

Here are some great **free extensions** (with more on the way) that enhance your experience with the business directory:

**Utility**

* [Toolbar](https://wordpress.org/plugins/connections-toolbar/) :: Provide quick links to the admin pages from the admin bar.
* [Login](https://wordpress.org/plugins/connections-business-directory-login/) :: Provides a simple to use login shortcode and widget.

**Custom Fields**

* [Business Open Hours](https://wordpress.org/plugins/connections-business-directory-hours/) :: Add the business open hours.
* [Local Time](https://wordpress.org/plugins/connections-business-directory-local-time/) :: Add the business local time.
* [Facilities](https://wordpress.org/plugins/connections-business-directory-facilities/) :: Add the business facilities.
* [Income Level](https://wordpress.org/plugins/connections-business-directory-income-levels/) :: Add an income level.
* [Education Level](https://wordpress.org/plugins/connections-business-directory-education-levels/) :: Add an education level.
* [Languages](https://wordpress.org/plugins/connections-business-directory-languages/) :: Add languages spoken.
* [Hobbies](https://wordpress.org/plugins/connections-business-directory-hobbies/) :: Add hobbies.

**Misc**

* [Face Detect](https://wordpress.org/plugins/connections-business-directory-face-detect/) :: Applies face detection before cropping an image.

**Premium Extensions**

* [Authored](https://connections-pro.com/add-on/authored/) :: Displays a list of blog posts written by the entry on their profile page.
* [Contact](https://connections-pro.com/add-on/contact/) :: Displays a contact form on the entry's profile page to allow your visitors to contact the entry without revealing their email address.
* [CSV Import](https://connections-pro.com/add-on/csv-import/) :: Bulk import your data in to your directory.
* [Custom Category Order](https://connections-pro.com/add-on/custom-category-order/) :: Order your categories exactly as you need them.
* [Custom Entry Order](https://connections-pro.com/add-on/custom-entry-order/) :: Allows you to easily define the order that your business directory entries should be displayed.
* [Enhanced Categories](https://connections-pro.com/add-on/enhanced-categories/) :: Adds many features to the categories.
* [Form](https://connections-pro.com/add-on/form/) :: Allow site visitor to submit entries to your directory. Also provides frontend editing support.
* [Link](https://connections-pro.com/add-on/link/) :: Links a WordPress user to an entry so that user can maintain their entry with or without moderation.
* [ROT13 Encryption](https://connections-pro.com/add-on/rot13-email-encryption/) :: Protect email addresses from being harvested from your business directory by spam bots.
* [SiteShot](https://connections-pro.com/add-on/siteshot/) :: Show a screen capture of the entry's website.
* [Widget Pack](https://connections-pro.com/add-on/widget-pack/) :: A set of feature rich, versatile and highly configurable widgets that can be used to enhance your directory.

== Installation ==

= Using the WordPress Plugin Search =

1. Navigate to the `Add New` sub-page under the Plugins admin page.
2. Search for `connections business directory anniversary and birthday emails`.
3. The plugin should be listed first in the search results.
4. Click the `Install Now` link.
5. Lastly click the `Activate Plugin` link to activate the plugin.

= Uploading in WordPress Admin =

1. [Download the plugin zip file](https://wordpress.org/plugins/connections-business-directory-anniversary-and-birthday-emails/) and save it to your computer.
2. Navigate to the `Add New` sub-page under the Plugins admin page.
3. Click the `Upload` link.
4. Select Connections Business Directory Facilities zip file from where you saved the zip file on your computer.
5. Click the `Install Now` button.
6. Lastly click the `Activate Plugin` link to activate the plugin.

= Using FTP =

1. [Download the plugin zip file](https://wordpress.org/plugins/connections-business-directory-anniversary-and-birthday-emails/) and save it to your computer.
2. Extract the Connections Business Directory Facilities zip file.
3. Create a new directory named `connections-business-directory-anniversary-and-birthday-email` directory in the `../wp-content/plugins/` directory.
4. Upload the files from the folder extracted in Step 2.
4. Activate the plugin on the Plugins admin page.

== Frequently Asked Questions ==

None yet...

== Screenshots ==

1. Option to send anniversary or birthday emails or both.
2. Configurable admin notification of when an anniversary or birthday email has been sent.
3. Configurable recipient email for anniversary and birthday.
4. The email template tags which can be used in all emails subjects and message bodies so you can customize the message for the recipient.
5. A few advanced email settings options.

== Changelog ==

= 1.0.4 04/20/2021 =
* TWEAK: Remove use of `create_function()`.
* OTHER: Correct plugin naming to match current naming convention.
* OTHER: Correct phpDoc.
* OTHER: Update copyright year.
* OTHER: Update `http` links to `https`.
* DEV: Add todo.
* DEV: Update README.txt plugin header.

= 1.0.3 07/23/2018 =
* BUG: Fix issue which could prevent admin notifications from being sent.
* I18N: Update Dutch (Netherlands) translation. props Age

= 1.0.2 07/23/2018 =
* TWEAK: Set Reply-To email header for third party plugin compatibility.
* I18N: Ensure the anniversary and birthday email tokens are translated.
* I18N: Add Dutch (Netherlands) translation. props Age
* DEV: Add some additional logging for use with debugging.

= 1.0.1 06/12/2018 =
* TWEAK: Use `cnText_Domain::register()` to load the translation text domain.
* OTHER: Corrected misspelling.
* I18N: Add the POT file.

= 1.0 02/20/2018 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
It is recommended to backup before updating. Requires WordPress >= 4.5.3 and PHP >= 5.3. PHP version >= 7.1 recommended.

= 1.0.1 =
It is recommended to backup before updating. Requires WordPress >= 4.5.3 and PHP >= 5.3. PHP version >= 7.1 recommended.

= 1.0.2 =
It is recommended to backup before updating. Requires WordPress >= 4.5.3 and PHP >= 5.3. PHP version >= 7.1 recommended.

= 1.0.3 =
It is recommended to backup before updating. Requires WordPress >= 4.5.3 and PHP >= 5.4. PHP version >= 7.1 recommended.

= 1.0.4 =
It is recommended to backup before updating. Requires WordPress >= 5.1 and PHP >= 5.6.20 PHP version >= 7.2 recommended.
