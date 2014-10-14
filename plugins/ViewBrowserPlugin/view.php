<?php
if (!extension_loaded('xsl')) {
    echo 'The xsl extension must be installed';
    exit;
}

if (!(isset($_GET["m"]) && ctype_digit($_GET["m"]))) {
    echo 'A numeric message id must be specified';
    exit;
}

if (getConfig('viewbrowser_anonymous')) {
    $uid = isset($_GET["uid"]) ? $_GET["uid"] : '';
} else {
    if (!isset($_GET["uid"])) {
        echo 'A user uid must be specified';
        exit;
    }
    $uid = $_GET["uid"];
}

if (!(isset($plugins['CommonPlugin']))) {
    echo 'CommonPlugin must be installed';
    exit;
}
error_reporting(-1);
require 'admin/sendemaillib.php';
require_once $plugins['CommonPlugin']->coderoot . 'Autoloader.php';
$email = $plugins['ViewBrowserPlugin']->createEmail($_GET["m"], $uid);

ob_end_clean();
header('Content-Type: text/html; charset=UTF-8');
echo $email;
