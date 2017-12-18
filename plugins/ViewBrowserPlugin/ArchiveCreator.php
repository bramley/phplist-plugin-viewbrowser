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

use phpList\plugin\Common\Controller as CommonController;
use phpList\plugin\Common\Listing;
use phpList\plugin\Common\PageLink;
use phpList\plugin\Common\Paginator;
use phpList\plugin\Common\Populator;

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

    private function urlPattern()
    {
        $params = $_GET;
        unset($params['start']);

        return sprintf('./?%s&start=%s', http_build_query($params), Paginator::NUM_PLACEHOLDER);
    }

    private function archiveItems($uid, $campaigns)
    {
        global $pageroot;

        $items = [];

        foreach ($campaigns as $c) {
            $query = http_build_query(
                ['pi' => $_GET['pi'], 'p' => 'view', 'm' => $c['messageid'], 'uid' => $uid],
                '',
                '&'
            );
            $url = sprintf('%s/?%s', $pageroot, $query);
            $link = new PageLink($url, $c['subject'], ['target' => '_blank']);
            $items[] = [
                'id' => $c['messageid'],
                'subject' => $c['subject'],
                'url' => $url,
                'link' => $link,
                'entered' => date_format(date_create($c['entered']), 'd/m/y'),
            ];
        }

        return $items;
    }

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Generate the listing of campaigns sent to a subscriber.
     *
     * @param int $uid the user unique id
     *
     * @return string the generated html
     */
    public function createArchive($uid)
    {
        $user = $this->dao->userByUniqid($uid);

        $itemsPerPage = getConfig('viewbrowser_archive_items_per_page');
        $startPage = isset($_GET['start']) ? $_GET['start'] : 1;
        $paginator = new Paginator($this->dao->totalMessagesForUser($uid), $itemsPerPage, $startPage, $this->urlPattern());
        $campaigns = $this->dao->messagesForUser($uid, ($startPage - 1) * $itemsPerPage, $itemsPerPage);

        $customCssUrl = getConfig('viewbrowser_archive_custom_css_url');
        $cssUrl = $customCssUrl ?: \ViewBrowserPlugin::CSS_URL;

        return $this->render(
            __DIR__ . '/archive.tpl.php',
            ['items' => $this->archiveItems($uid, $campaigns), 'email' => $user['email'], 'paginator' => $paginator, 'css' => $cssUrl]
        );
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
        $user = $this->dao->subscriberForAdmin($adminId);

        if ($user === false) {
            return s('No campaigns found');
        }
        $uid = $user['uniqid'];
        $title = s('Campaigns sent to %s', $user['email']);
        $populateCallback = function (\WebblerListing $w, $start, $limit) use ($uid, $title) {
            $campaigns = $this->dao->messagesForUser($uid, $start, $limit);
            $w->title = $title;
            $w->elementHeading = s('ID');

            foreach ($this->archiveItems($uid, $campaigns) as $row) {
                $key = $row['id'];
                $w->addElement($key);
                $w->addColumn($key, s('Sent'), $row['entered']);
                $w->addColumn($key, s('Campaign'), $row['subject'], $row['url'], '', ['target' => '_blank']);
            }
        };
        $totalCallback = function () use ($uid) {
            return $this->dao->totalMessagesForUser($uid);
        };
        $populator = new Populator($populateCallback, $totalCallback);
        $listing = new Listing(new Controller(), $populator);
        $itemsPerPage = getConfig('viewbrowser_archive_items_per_page');
        $listing->pager->setItemsPerPage([$itemsPerPage], $itemsPerPage);

        return $listing->display();
    }
}

class Controller extends CommonController
{
}
