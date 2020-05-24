# View in Browser Plugin #

## Description ##

The plugin generates a page that displays a campaign email customised with subscriber and phplist placeholders, and
link tracking.
A placeholder, [VIEWBROWSER], is included in a campaign and is replaced by a link to the page when the email is created by phplist.
Alternatively the placeholder [VIEWBROWSERURL] can be used for the URL of the page.

The plugin can also generate an anonymous page, where subscriber placeholders are removed.

It also provides an archive page that lists all emails sent to a subscriber.
That page is accessed by including a placeholder, [ARCHIVE] or [ARCHIVEURL], in a campaign email.

## Installation ##

### Dependencies ###

Requires phplist release 3.0.7 or later and php version 5.5 or later.

This plugin requires the Common Plugin v3.10.0 or later to be installed, and will not work without that.
phplist now includes Common Plugin so you should only need to enable it on the Manage Plugins page.

Requires the XSL extension to be included in php. You can verify this through phpinfo.

### Install through phplist ###
Install on the Plugins page (menu Config > Manage Plugins) using the package URL `https://github.com/bramley/phplist-plugin-viewbrowser/archive/master.zip`.
Then click the button to enable the plugin.

### Install manually ###
Download the plugin zip file from <https://github.com/bramley/phplist-plugin-viewbrowser/archive/master.zip>

Expand the zip file, then copy the contents of the plugins directory to your phplist plugins directory.
This should contain

* the file ViewBrowserPlugin.php
* the directory ViewBrowserPlugin

Then click the button to enable the plugin.

## Usage ##

For guidance on using the plugin see the plugin's page within the phplist documentation site <https://resources.phplist.com/plugin/viewinbrowser>

## Support ##

Please raise any questions or problems in the user forum <https://discuss.phplist.org/>.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    2.11.3+20200524 Allow a superadmin to view any campaign.
    2.11.2+20200523 Add the date placeholder plugin to the list of supported plugins
    2.11.1+20200519 Replace USERID placeholder as a synonym for UNIQID
    2.11.0+20200417 Use core phplist functions to format dates.
    2.10.4+20191108 Remove support for phplist versions prior to 3.0.7
    2.10.3+20190907 Restrict downloading attachments to subscribers only
    2.10.2+20190604 Replace CONTACT and CONTACTURL placeholders
    2.10.1+20181122 Change to the messages that a subscriber can view
    2.10.0+20181023 Provide anonymous archive of emails sent to a list
    2.9.6+20181015  German translation
    2.9.5+20181015  Minor bug fix
    2.9.4+20180918  Remove urls for sensitive placeholders for an anonymous page
    2.9.3+20180419  Add description for the Plugins page
    2.9.2+20180207  Add Spanish translation
    2.9.1+20171220  Update dependency on Common Plugin
    2.9.0+20171218  Use paging on the archive pages
    2.8.0+20171206  Rework layout of campaign archive
    2.7.1+20171120  Added Dutch translations
    2.7.0+20171102  Add archive page and placeholders
    2.6.0+20170604  Use dependency injection container
    2.5.1+20170331  Remove placeholders when message is forwarded
    2.5.0+20170204  Changes to link tracking for phplist 3.3.0
    2.4.5+20160714  Convert text footer to html
    2.4.4+20160120  Replace LOGO only for compatible versions of phplist
    2.4.3+20151215  Correction for LOGO placeholder
    2.4.2+20151214  Support LOGO placeholder
    2.4.1+20151121  Update dependencies
    2.4.0+20151119  Integration with other plugins
    2.3.0+20151106  Suppress VIEWBROWSER link
                    Minor bug fixes
    2.2.0+20151018  Internal changes to use namespaced classes
    2.1.3+20150916  Display [CONTENT] when used by Content Areas plugin
    2.1.2+20150907  Fix problem introduced with previous change
    2.1.1+20150906  Fix problem where default settings are not used
    2.1.0+20150819  Display attachments with download links
    2.0.1+20150817  Fix backslashes being displayed
    2.0.0+20150815  Updated dependencies
    2015-07-29      Allow additional attributes on the link <a> element
    2015-06-25      Internal changes
    2015-03-23      Change to autoload approach
    2015-02-06      Use & as arg separator for http_build_query()
    2014-11-18      Allow text to be translated
    2014-10-14      Provide anonymous page, support sending campaign from a webpage
    2014-10-02      Keep the original document type
    2014-09-18      Handle embedded template images, allow other plugins to transform the email
    2014-08-16      The view page is now a plugin public page for phplist 3.0.7
    2014-08-09      Test for xsl extension being installed
    2014-05-05      Added fromemail placeholder
    2014-04-17      An email is now always personalised
    2014-04-14      Support for click tracking and further placeholders
    2014-04-12      Support for user tracking
    2014-04-09      Added to GitHub
