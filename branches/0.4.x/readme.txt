=== Job Manager ===
Contributors: pento
Donate link: http://pento.net/donate/
Tags: jobs, manager, list, listing, employer, application
Requires at least: 2.9
Tested up to: 2.9.1
Stable tag: trunk

A job listing and job application management plugin for WordPress.

== Description ==

A plugin for managing job lists and job applications on your WordPress site. It supports all the features you need to manage your organisation's job openings.

***Important Note***: If you're upgrading from version 0.3.3 or earlier, *please* read the [upgrade documentation](http://code.google.com/p/wordpress-job-manager/wiki/UpgradingFrom03x). There have been significant changes that you need to know about.

*    *Job Listing*
    *    Categories to create multiple job lists
    *    Jobs can be filed under multiple categories
    *    Icons can be assigned to jobs, to make them stand out in the listing
*    *Job Management*
    *    Jobs can be defined to display between certain dates, or indefinitely
    *    Simple admin interface for editing, updating and creating jobs
*    *Applications*
    *    Applicants can apply through the website, using a form that you can customize, so you get the information you need
    *    Advanced filtering on application forms, to ensure you only get applications that match your criteria: [Documentation](http://code.google.com/p/wordpress-job-manager/wiki/CustomApplicationForm)
    *    Upon successful application, you can be emailed the details, so you're always up to date with new applicants
*    *Applicant Management*
    *    Simple interface for viewing all applicants
    *    List can be filtered based on any criteria in your custom application form
    *    Email individual or groups of candidates, to keep them updated on new job opportunities in your organisation

Related links:

* [Plugin Homepage](http://pento.net/projects/wordpress-job-manager-plugin/)
* [Support Forum](http://wordpress.org/tags/job-manager?forum_id=10)
* [Report Bugs and Request Features](http://code.google.com/p/wordpress-job-manager/issues/list)
* [Development Roadmap](http://code.google.com/p/wordpress-job-manager/wiki/Roadmap)

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make sure the `uploads` and `icons` directories are writeable by the web server

== Frequently Asked Questions ==

= How do I setup a custom application form? =

For a full description of how to use the application form customization features, please read [this page in the documentation](http://code.google.com/p/wordpress-job-manager/wiki/CustomApplicationForm).

== Other Plugin Support ==

The Job Manager supports added functionality when other plugins are installed. If you think your plugin could add some functionality to Job Manager, please [submit a feature request](http://code.google.com/p/wordpress-job-manager/issues/list).

= Google XML Sitemaps =

Job Manager will add all of your job lists and job detail pages to your sitemap, when [Google XML Sitemaps](http://wordpress.org/extend/plugins/google-sitemap-generator/) is installed on your site.

== Changelog ==

= 0.4.7 =
* FIXED: Empty job list message not displaying correctly
* FIXED: New job showing a bad start date
* FIXED: Some PHP notices
* FIXED: Template from main page not being used correctly
* FIXED: Removed 5 job limit from display code

= 0.4.6 =
* FIXED: Application email not being sent correctly
* FIXED: Not displaying if used with a theme that doesn't have a page.php
* FIXED: Broken XHTML tag in admin
* FIXED: Jobs with no icon had a broken icon displaying
* FIXED: 'Job: ' job title prefix displaying in wrong place
* FIXED: Escape error message in application form setup
* FIXED: Escape default values in application form display
* FIXED: Custom filter error messages not displaying
* FIXED: `<title>` not being displayed correctly
* FIXED: Some PHP notices

= 0.4.5 =
* FIXED: Job list not displaying under some circumstances
* FIXED: Not retrieving job list in category pages

= 0.4.4 =
* FIXED: Job permalinks now being treated as pages
* FIXED: Jobs/application form not showing if main jobs page was set as a child page
* FIXED: Not all applications displaying in application list
* FIXED: Permalinks now allow for a lack of trailing '/'
* FIXED: Application field sort order not being obeyed
* FIXED: Job link not being display in application list
* FIXED: Category pages not storing correctly

= 0.4.3 =
* FIXED: Removed some references to the old code removed in 0.4.2

= 0.4.2 =
* FIXED: Google XML Sitemap option not showing correctly
* FIXED: Incorrect check could cause plugin activation to fail
* FIXED: Removed some dead code

= 0.4.1 =
* FIXED: Application fields not saving properly
* FIXED: Miscellaneous PHP warnings
* FIXED: Upload directory write check failing under some circumstances

= 0.4.0 =
* ADDED: Check to make sure data directories are writeable by the plugin
* ADDED: Nonce fields are now in all Admin forms, for added security
* ADDED: Ability to delete jobs
* ADDED: Ability to change the page template used
* CHANGED: Job Manager now requires WordPress 2.9 or higher
* CHANGED: All data is now stored in default WordPress tables
* CHANGED: All options are now stored in a single wp_options entry
* FIXED: A job being displayed could include an incorrect <title>
* FIXED: No longer re-write the .htaccess file. Unnecessary, and was causing problems on 1&1 hosting.
* FIXED: Problem with including symlinked files
* FIXED: Secured the uploaded files directory
* FIXED: Link to files in the Application List

= 0.3.3 =
* FIXED: SQL errors when deleting applications

= 0.3.2 =
* FIXED: SQL error when submitting an application

= 0.3.1 =
* FIXED: A default value for Category slugs is now inserted. Upgrading will create default slugs if no slug exists.
* FIXED: Bug preventing icons from being deleted.
* FIXED: Code cleanup

= 0.3.0 =
* ADDED: Framework for supporting extra functionality through other plugins
* ADDED: Google Sitemap support, through the [Google XML Sitemaps](http://wordpress.org/extend/plugins/google-sitemap-generator/) plugin.
* ADDED: POT file, for translations
* FIXED: Potential Application submission error
* FIXED: Storing incorrect information if no file was uploaded
* FIXED: Logic bug in plugin activation
* FIXED: Options upgrade function wasn't being called
* FIXED: Minor string fixes

= 0.2.4 =
* FIXED: Still some circumstances where jobs weren't displaying
* FIXED: Removed some CSS that should be in a site's main.css

= 0.2.3 =
* FIXED: Jobs were not displaying if the start or end date was empty.

= 0.2.2 =
* FIXED: Applications without an associated job were not being stored correctly.
* FIXED: Minor bugs with filtering applications.

= 0.2.1 =
* FIXED: Bad homepage link

= 0.2.0 =
* ADDED: Ability to switch between summary and full view for the Job List

= 0.1.0 =
* Initial release

== Upgrade Notice ==
