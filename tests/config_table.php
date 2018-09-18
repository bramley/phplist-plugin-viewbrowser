<?php
/*
 * Hold config table entries as a global so that they can be overridden by individual tests
 */

global $phplist_config;

$phplist_config = [
    'version' => '3.3.4',
    'website' => 'mysite.com',
    'domain' => 'mysite.com',
    'viewbrowser_link' => 'View in your browser',
    'viewbrowser_attributes' => '',
    'viewbrowser_plugins' => "ContentAreas\nconditionalPlaceholderPlugin\nRssFeedPlugin\nViewBrowserPlugin",
    'viewbrowser_anonymous' => false,
    'viewbrowser_archive_link' => 'archive',
    'viewbrowser_archive_styles' => '',
    'html_email_style' => '<style></style>',
    'subscribeurl' => '',
    'unsubscribeurl' => 'http://mysite.com/lists/?p=unsubscribe',
    'preferencesurl' => 'http://mysite.com/lists/?p=preferences',
    'forwardurl' => 'http://mysite.com/lists/?p=forward',
    'confirmationurl' => '',
    'blacklisturl' => '',
    'viewbrowser_archive_items_per_page' => 5,
    'viewbrowser_archive_custom_css_url' => '',
];
