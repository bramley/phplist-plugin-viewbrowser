<?php

namespace phpList\plugin\ViewBrowserPlugin;

use phpList\plugin\Common;

if (!(isset($_GET['id']) && ctype_digit($_GET['id']))) {
    echo s('A numeric template image id must be specified');
    exit;
}
error_reporting(-1);

$dao = new DAO(new Common\DB());
$row = $dao->templateImageById($_GET['id']);

ob_end_clean();

if ($row) {
    $mime = ($row['mimetype']) ? $row['mimetype'] : 'image/jpeg';
    $data = $row['data'];
} else {
    $mime = 'image/png';
    $data = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAABGdBTUEAALGPC/xhBQAAAAZQTFRF////AAAAVcLTfgAAAAF0Uk5TAEDm2GYAAAABYktHRACIBR1IAAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH0gQCEx05cqKA8gAAAApJREFUeJxjYAAAAAIAAUivpHEAAAAASUVORK5CYII=';
}
$data = base64_decode($data);
header('Content-type: ' . $mime);
header('Content-Length: ' . strlen($data));
echo $data;
exit;
