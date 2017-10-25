<?php

namespace phpList\plugin\ViewBrowserPlugin;

$container = include __DIR__ . '/dic.php';
$archive = $container->get('ArchiveCreator');
echo $archive->createArchiveForAdmin($_SESSION['logindetails']['id']);
