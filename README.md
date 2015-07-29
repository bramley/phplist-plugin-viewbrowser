# View in Browser Plugin #

## Description ##

The plugin generates a page that displays a campaign email customised with subscriber and phplist placeholders, and 
link tracking.
A placeholder, [VIEWBROWSER], is included in a campaign and is replaced by a link to the page when the email is created by phplist.
Alternatively the placeholder [VIEWBROWSERURL] can be used for the URL of the page.

The plugin can also generate an anonymous page, where subscriber placeholders are removed.

## Installation ##

### Dependencies ###

Requires php version 5.3 or later.

Requires the Common Plugin version 2015-03-23 or later to be installed. You should install or upgrade to the latest version.

See <https://github.com/bramley/phplist-plugin-common>

Requires the XSL extension to be included in php. You can verify this through phpinfo.

### Set the plugin directory ###
The default plugin directory is `plugins` within the phplist `admin` directory but you can use a directory outside of the web root by
changing the definition of `PLUGIN_ROOTDIR` in config.php.
The benefit of this is that plugins will not be affected when you upgrade phplist.

### Install through phplist ###
Install on the Plugins page (menu Config > Manage Plugins) using the package URL `https://github.com/bramley/phplist-plugin-viewbrowser/archive/master.zip`.

In phplist releases 3.0.5 and earlier there is a bug that can cause a plugin to be incompletely installed on some configurations (<https://mantis.phplist.com/view.php?id=16865>). 
Check that these files are in the plugin directory. If not then you will need to install manually. The bug has been fixed in release 3.0.6.

* the file ViewBrowserPlugin.php
* the directory ViewBrowserPlugin

Then click the small orange icon to enable the plugin.

### Install manually ###
Download the plugin zip file from <https://github.com/bramley/phplist-plugin-viewbrowser/archive/master.zip>

Expand the zip file, then copy the contents of the plugins directory to your phplist plugins directory.
This should contain

* the file ViewBrowserPlugin.php
* the directory ViewBrowserPlugin

Then click the small orange icon to enable the plugin.

### Install view.php (phplist 3.0.6 and earlier) ###

This step is necessary only for phplist releases 3.0.6 and earlier.

Copy the file `view_3.0.6.php` from the ViewBrowserPlugin directory to the phplist directory - this is usually `/lists`,
and rename to `view.php`.

Amend .htaccess in the phplist directory to allow the file to be accessed. Change this line

    <FilesMatch "(index.php|dl.php|ut.php|lt.php|download.php|connector.php)$">
to

    <FilesMatch "(index.php|dl.php|ut.php|lt.php|download.php|connector.php|view.php)$">

## Configuration ##
On the Settings page you can specify the link text, such as "View this email in your browser".

You can change the styling of the link by specifying additional attributes for the <a> element,
such as a specific class or a custom style. For example

    class="myclass"
or

    style="color: #ea5b0c;"

You can also specify whether the plugin should generate anonymous pages. The default value for this is no.

## Usage ##
### Placeholders ###
Include the placeholder [VIEWBROWSER] in a message or template. When phplist generates the emails for the campaign, the placeholder
will be replaced by a link (an HTML `<a>` element) to the view page. The link URL includes the message id and the subscriber's uid.

When a subscriber clicks the link the plugin generates the email as a web page using the message, the template (if used),
and by replacing placeholders.

Alternatively you can use the [VIEWBROWSERURL] placeholder. This is replaced by the same URL that is used for the [VIEWBROWSER] link.

### Anonymous pages ###

You can use a link to an anonymous page, which has the subscriber placeholders removed, by using a URL of this format but customised
for the actual location of phplist and the campaign id

    http://www.mysite.com/lists/?m=36&p=view&pi=ViewBrowserPlugin

This URL can be used outside of phplist and will allow anyone to view the campaign email.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    2015-07-29  Allow additional attributes on the link <a> element
    2015-06-25  Internal changes
    2015-03-23  Change to autoload approach
    2015-02-06  Use & as arg separator for http_build_query()
    2014-11-18  Allow text to be translated
    2014-10-14  Provide anonymous page, support sending campaign from a webpage
    2014-10-02  Keep the original document type
    2014-09-18  Handle embedded template images, allow other plugins to transform the email
    2014-08-16  The view page is now a plugin public page for phplist 3.0.7
    2014-08-09  Test for xsl extension being installed
    2014-05-05  Added fromemail placeholder
    2014-04-17  An email is now always personalised
    2014-04-14  Support for click tracking and further placeholders
    2014-04-12  Support for user tracking
    2014-04-09  Added to GitHub
