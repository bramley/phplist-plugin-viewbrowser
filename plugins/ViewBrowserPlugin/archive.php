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

$container = include __DIR__ . '/dic.php';
$archive = $container->get('ArchiveCreator');

if (!empty($_GET['uid'])) {
    displayPublicPage($archive->createSubscriberArchive($_GET['uid']));

    return;
}

if ((isset($_GET['list']) && ctype_digit($_GET['list']))) {
    $result = getConfig('viewbrowser_anonymous')
        ? $archive->createListArchive($_GET['list'])
        : s('Not allowed to view campaigns for list %d', $_GET['list']);
} else {
    $result = s('A user uid or a list id must be specified');
}
displayPublicPage($result);
