# View in Browser Plugin #

## Description ##

The plugin provides a placeholder, [VIEWBROWSER], that allows subscribers to view an email in their browser.

## Installation ##

### Dependencies ###

Requires php version 5.3 or later.

Requires the Common Plugin to be installed. See <https://github.com/bramley/phplist-plugin-common>

Requires the XSL extension to be included in php. You can verify this through phpinfo.

### Set the plugin directory ###
You can use a directory outside of the web root by changing the definition of `PLUGIN_ROOTDIR` in config.php.
The benefit of this is that plugins will not be affected when you upgrade phplist.

### Install through phplist ###
Install on the Plugins page (menu Config > Plugins) using the package URL `https://github.com/bramley/phplist-plugin-viewbrowser/archive/master.zip`.

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

### Install view.php ###
Copy the file `view.php` from the ViewBrowserPlugin directory to the phplist directory - this is usually `/lists`.

Amend .htaccess in the phplist directory to allow the file to be accessed. Change this line

    <FilesMatch "(index.php|dl.php|ut.php|lt.php|download.php|connector.php)$">
to

    <FilesMatch "(index.php|dl.php|ut.php|lt.php|download.php|connector.php|view.php)$">

## Configuration ##
On the Settings page you can specify the link text, such as "View this email in your browser".

## Usage ##
Include the placeholder [VIEWBROWSER] in a message or template. When phplist generates the emails for the campaign, the placeholder
will be replaced by a link (an HTML `<a>` element) to the view page. The link URL includes the message id and the subscriber's uid.

When a subscriber clicks the link the plugin generates the email as a web page using the message, the template (if used),
and by replacing placeholders.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    2014-08-09  Test for xsl extension being installed
    2014-05-05  Added fromemail placeholder
    2014-04-17  An email is now always personalised
    2014-04-14  Support for click tracking and further placeholders
    2014-04-12  Support for user tracking
    2014-04-09  Added to GitHub
