<?php
if (!extension_loaded('xsl')) {
    echo 'The xsl extension must be installed';
    exit;
}

if (!(isset($_GET["m"]) && ctype_digit($_GET["m"]))) {
    echo 'A numeric message id must be specified';
    exit;
}

if (!(isset($_GET["uid"]) && strlen($_GET["uid"]) == 32)) {
    echo 'A valid user uid must be specified';
    exit;
}

if (!(isset($plugins['CommonPlugin']))) {
    echo 'CommonPlugin must be installed';
    exit;
}
error_reporting(-1);
require 'admin/sendemaillib.php';
require_once $plugins['CommonPlugin']->coderoot . 'Autoloader.php';
$email = $plugins['ViewBrowserPlugin']->createEmail($_GET["m"], $_GET["uid"]);

ob_end_clean();
header('Content-Type: text/html; charset=UTF-8');
echo $email;
