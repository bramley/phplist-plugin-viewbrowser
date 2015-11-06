<?php

ob_start();
$er = error_reporting(-1);
require_once dirname(__FILE__) .'/admin/commonlib/lib/unregister_globals.php';
require_once dirname(__FILE__) .'/admin/commonlib/lib/magic_quotes.php';

## none of our parameters can contain html for now
$_GET = removeXss($_GET);
$_POST = removeXss($_POST);
$_REQUEST = removeXss($_REQUEST);
$_COOKIE = removeXss($_COOKIE);

if (isset($_SERVER['ConfigFile']) && is_file($_SERVER['ConfigFile'])) {
    include $_SERVER['ConfigFile'];
} elseif (is_file('config/config.php')) {
    include 'config/config.php';
} else {
    print "Error, cannot find config file\n";
    exit;
}

require_once dirname(__FILE__).'/admin/init.php';

$GLOBALS['database_module'] = basename($GLOBALS['database_module']);
$GLOBALS['language_module'] = basename($GLOBALS['language_module']);

require_once dirname(__FILE__).'/admin/'.$GLOBALS['database_module'];

# load default english and language
include_once dirname(__FILE__).'/texts/english.inc';
# Allow customisation per installation
if (is_file($_SERVER['DOCUMENT_ROOT'].'/'.$GLOBALS['language_module'])) {
    include_once $_SERVER['DOCUMENT_ROOT'].'/'.$GLOBALS['language_module'];
}

include_once dirname(__FILE__).'/admin/languages.php';
require_once dirname(__FILE__).'/admin/defaultconfig.php';
require_once dirname(__FILE__).'/admin/connect.php';
include_once dirname(__FILE__).'/admin/lib.php';
include_once dirname(__FILE__).'/admin/sendemaillib.php';

if (!extension_loaded('xsl')) {
    echo s('The xsl extension must be installed');
    exit;
}

if (!(isset($_GET['m']) && ctype_digit($_GET['m']))) {
    echo s('A numeric message id must be specified');
    exit;
}

if (getConfig('viewbrowser_anonymous')) {
    $uid = isset($_GET['uid']) ? $_GET['uid'] : '';
} else {
    if (!isset($_GET['uid'])) {
        echo s('A user uid must be specified');
        exit;
    }
    $uid = $_GET['uid'];
}

if (!(isset($plugins['CommonPlugin']))) {
    echo s('CommonPlugin must be installed');
    exit;
}

error_reporting(-1);
require_once $plugins['CommonPlugin']->coderoot . 'Autoloader.php';
$creator = new phpList\plugin\ViewBrowserPlugin\ContentCreator();

ob_end_clean();
header('Content-Type: text/html; charset=UTF-8');
echo $creator->createContent($_GET['m'], $uid);
