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

use phpList\plugin\Common\Listing;
use phpList\plugin\Common\PageLink;
use phpList\plugin\Common\Paginator;
use phpList\plugin\Common\Populator;
use phpList\plugin\Common\View;

use function phpList\plugin\Common\publicUrl;

/**
 * Class to create an archive page.
 */
class ArchiveCreator
{
    /** @var phpList\plugin\ViewBrowserPlugin\DAO DAO */
    private $dao;

    private function urlPattern()
    {
        $params = $_GET;
        unset($params['start']);

        return sprintf('./?%s&start=%s', http_build_query($params), Paginator::NUM_PLACEHOLDER);
    }

    /**
     * Create fields for an archive item from campaign fields.
     *
     * @param string   $uid       user unique id
     * @param iterable $campaigns
     *
     * @return Generator
     */
    private function archiveItems($uid, $campaigns)
    {
        foreach ($campaigns as $c) {
            $params = ['pi' => $_GET['pi'], 'p' => 'view', 'm' => $c['messageid']];

            if ($uid) {
                $params['uid'] = $uid;
            }
            $url = publicUrl($params);
            $link = new PageLink($url, $c['subject'], ['target' => '_blank']);

            yield [
                'id' => $c['messageid'],
                'subject' => $c['subject'],
                'url' => $url,
                'link' => $link,
                'entered' => formatDate($c['entered']),
            ];
        }
    }

    private function genericCreateArchive($uid, $totalCallback, $resultsCallback, $subject)
    {
        $itemsPerPage = getConfig('viewbrowser_archive_items_per_page');
        $startPage = isset($_GET['start']) ? $_GET['start'] : 1;
        $paginator = new Paginator(
            $totalCallback(),
            $itemsPerPage,
            $startPage,
            $this->urlPattern()
        );
        $campaigns = $resultsCallback(($startPage - 1) * $itemsPerPage, $itemsPerPage);

        $customCssUrl = getConfig('viewbrowser_archive_custom_css_url');
        $cssUrl = $customCssUrl ?: \ViewBrowserPlugin::CSS_URL;

        return (string) new View(
            __DIR__ . '/archive.tpl.php',
            ['items' => $this->archiveItems($uid, $campaigns), 'subject' => $subject, 'paginator' => $paginator, 'css' => $cssUrl]
        );
    }

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Generate the listing of campaigns sent to a subscriber.
     *
     * @param string $uid the user unique id
     *
     * @return string the generated html
     */
    public function createSubscriberArchive($uid)
    {
        $user = $this->dao->userByUniqid($uid);

        $totalCallback = function () use ($uid) {
            return $this->dao->totalMessagesForUser($uid);
        };
        $resultsCallback = function ($start, $limit) use ($uid) {
            return $this->dao->messagesForUser($uid, $start, $limit);
        };

        return $this->genericCreateArchive($uid, $totalCallback, $resultsCallback, $user['email']);
    }

    /**
     * Generate the listing of campaigns sent to a list.
     * The campaigns are displayed anonymously.
     *
     * @param int $listId the list id
     *
     * @return string the generated html
     */
    public function createListArchive($listId)
    {
        $list = $this->dao->listById($listId);

        if (!$list) {
            return s('List %d does not exist', $listId);
        }
        $allowedLists = getConfig('viewbrowser_allowed_lists');
        $allowed =
            ($allowedLists == '' && $list['active'] == 1)
            || in_array($listId, preg_split('/\s+/', $allowedLists, -1, PREG_SPLIT_NO_EMPTY));

        if (!$allowed) {
            return s('Not allowed to view campaigns for list %d', $listId);
        }

        $totalCallback = function () use ($listId) {
            return $this->dao->totalMessagesForList($listId);
        };
        $resultsCallback = function ($start, $limit) use ($listId) {
            return $this->dao->messagesForList($listId, $start, $limit);
        };

        return $this->genericCreateArchive('', $totalCallback, $resultsCallback, $list['name']);
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
        $listing = new Listing($populator);
        $itemsPerPage = getConfig('viewbrowser_archive_items_per_page');
        $listing->pager->setItemsPerPage([$itemsPerPage], $itemsPerPage);

        return $listing->display();
    }
}
