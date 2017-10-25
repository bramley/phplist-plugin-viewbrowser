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
<?php echo getConfig('viewbrowser_archive_styles'); ?>
</style>
<div id="archive">
    <div class="table">
        <div class="line">
            <div class="innertable">
                <div class="cell-1 heading"><?= s('Campaign ID') ?></div>
                <div class="cell-2 heading"><?= s('Campaign subject') ?></div>
                <div class="cell-3 heading"><?= s('Date sent') ?></div>
            </div>
        </div>
<?php foreach ($items as $item): ?>
        <div class="line">
            <div class="innertable">
                <div class="cell-1"><?php echo $item['id']; ?></div>
                <div class="cell-2"><?php echo $item['link']; ?></div>
                <div class="cell-3"><?php echo $item['entered']; ?></div>
            </div>
        </div>
<?php endforeach; ?>
    </div>
</div>
