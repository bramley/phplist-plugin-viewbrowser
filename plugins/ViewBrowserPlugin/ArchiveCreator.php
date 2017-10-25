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

use phpList\plugin\Common\PageLink;

/**
 * Class to create an archive page.
 */
class ArchiveCreator
{
    /** @var phpList\plugin\ViewBrowserPlugin\DAO DAO */
    private $dao;

    private function render($template, $params)
    {
        extract($params);
        ob_start();
        require $template;

        return ob_get_clean();
    }

    private function archiveItems($uid)
    {
        global $pageroot;

        $campaigns = $this->dao->messagesForUser($uid);
        $items = [];

        foreach ($campaigns as $c) {
            $url = "$pageroot/?" . http_build_query(
                ['pi' => $_GET['pi'], 'p' => 'view', 'm' => $c['messageid'], 'uid' => $uid],
                '',
                '&'
            );
            $link = new PageLink($url, $c['subject'], ['target' => '_blank']);
            $items[] = ['id' => $c['messageid'], 'link' => $link, 'entered' => $c['entered']];
        }

        return $items;
    }

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Generate the listing of campaigns sent to the user.
     *
     * @param int $uid the user unique id
     *
     * @return string the generated html
     */
    public function createArchive($uid)
    {
        $items = $this->archiveItems($uid);

        return $this->render(__DIR__ . '/archive.tpl.php', ['items' => $items]);
    }

    /**
     * Generate the listing of campaigns sent to the current admin.
     *
     * @param int $adminId the current admin
     *
     * @return string the generated html
     */
    public function createArchiveForAdmin($adminId)
    {
        $uid = $this->dao->subscriberForAdmin($adminId);

        return $uid === false ? s('No campaigns found') : $this->createArchive($uid);
    }
}
