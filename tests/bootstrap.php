<?php

require __DIR__ . '/config_table.php';
require __DIR__ . '/phplist.php';

$GLOBALS['systemroot'] = '/home/duncan/www/lists';
define('PHPLISTINIT', 1);
define("PLUGIN_ROOTDIR", $GLOBALS['systemroot'] . '/admin/plugins');
define("PLUGIN_ROOTDIRS", "");
define('EMAILTEXTCREDITS', true);
define('ALWAYS_ADD_USERTRACK', true);
define('CLICKTRACK', true);
define('XORmask', '6f409c5681427eeaaaaa495797642e4b');
define('SIGN_WITH_HMAC', true);
define('HMACKEY', '1234567890123456');
define('HASH_ALGO', 'sha256');

$GLOBALS['public_scheme'] = 'http';
$GLOBALS['pageroot'] = '/lists';
$GLOBALS['website'] = getConfig('website');
$GLOBALS['domain'] = getConfig('domain');
$GLOBALS['strUnsubscribe'] = 'unsubscribe';
$GLOBALS['strThisLink'] = 'this link';
$GLOBALS['strForward'] = 'Forward to a friend';
$GLOBALS['PoweredByText'] = 'Powered by phplist';
$GLOBALS['PoweredByImage'] = 'mysite.com';
$GLOBALS['strForwardTitle'] = 'Forward a Message to Someone';
$GLOBALS['strToUnsubscribe'] = 'If you do not want to receive any more newsletters, ';
$GLOBALS['strToUpdate'] = 'To update your preferences and to unsubscribe visit';
$GLOBALS['strContactMessage'] = 'Add us to your address book';

$_GET['pi'] = 'ViewBrowserPlugin';

require __DIR__ . '/../plugins/ViewBrowserPlugin.php';
$pi = new ViewBrowserPlugin();
$pi->activate();

$GLOBALS['plugins'] = [
    'ViewBrowserPlugin' => $pi,
];
require PLUGIN_ROOTDIR . '/CommonPlugin/Autoloader.php';
