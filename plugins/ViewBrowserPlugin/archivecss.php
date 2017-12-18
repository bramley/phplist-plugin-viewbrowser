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

namespace phpList\plugin\ViewBrowserPlugin;

use phpList\plugin\Common\FileServer;

/**
 * Page to send the archive css file to the browser.
 */
$server = new FileServer();
ob_end_clean();
$server->serveFile($plugins['ViewBrowserPlugin']->coderoot . 'archive.css', 'text/css');

exit;
