<?php
/**
 * ViewBrowserPlugin for phplist.
 *
 * This file is a part of ViewBrowserPlugin.
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  phplist
 *
 * @author    Duncan Cameron
 * @copyright 2014-2017 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */
?>

<style>
<?= getConfig('viewbrowser_archive_styles'); ?>
</style>
<div id="archive">
    <div>
        <h4><?= s('Campaigns sent to %s', $email); ?></h4>
        <ul id="archive-list">
<?php foreach ($items as $item): ?>
            <li>
                <?= $item['entered']; ?> - <?= $item['link']; ?> <span class="campaign-id"><?= '[' . $item['id'] . ']'; ?></span>
            </li>
<?php endforeach; ?>
        </ul>
    </div>
</div>
