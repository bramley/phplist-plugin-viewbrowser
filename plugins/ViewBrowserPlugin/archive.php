<?php

namespace phpList\plugin\ViewBrowserPlugin;

function displayPublicPage($page)
{
    global $pagedata, $PoweredBy;

    $title = htmlspecialchars(s('Archive of campaigns'));

    echo <<<END
<title>$title</title>
{$pagedata['header']}
$page
$PoweredBy
{$pagedata['footer']}
END;
}

if (!isset($_GET['uid'])) {
    echo s('A user uid must be specified');
    exit;
}
$uid = $_GET['uid'];
$container = include __DIR__ . '/dic.php';
$archive = $container->get('ArchiveCreator');

ob_end_clean();
header('Content-Type: text/html; charset=UTF-8');
echo displayPublicPage($archive->createArchive($uid));
