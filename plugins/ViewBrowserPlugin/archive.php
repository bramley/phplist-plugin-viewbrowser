<?php

namespace phpList\plugin\ViewBrowserPlugin;

function displayPublicPage($page)
{
    global $pagedata, $PoweredBy;

    $title = htmlspecialchars(s('Campaign archive'));

    echo <<<END
<title>$title</title>
{$pagedata['header']}
$page
$PoweredBy
{$pagedata['footer']}
END;
}

if (empty($_GET['uid'])) {
    echo s('A user uid must be specified');
    exit;
}
$uid = $_GET['uid'];
$container = include __DIR__ . '/dic.php';
$archive = $container->get('ArchiveCreator');
echo displayPublicPage($archive->createArchive($uid));
