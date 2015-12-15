<?php

namespace phpList\plugin\ViewBrowserPlugin;

use phpList\plugin\Common;

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
 * @copyright 2014 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * DAO class providing access to the message table.
 */
class DAO extends Common\DAO\User
{
    public function forwardId($url)
    {
        $url = sql_escape($url);
        $sql =
            "SELECT id
            FROM {$this->tables['linktrack_forward']} AS ltf
            WHERE ltf.url = '$url'";

        return $this->dbCommand->queryOne($sql, 'id');
    }

    public function message($id)
    {
        $sql = sprintf('
            SELECT t.template
            FROM %s AS m
            LEFT JOIN %s AS t ON t.id = m.template
            WHERE m.id = %d',
            $this->tables['message'], $this->tables['template'], $id
        );

        return $this->dbCommand->queryRow($sql);
    }

    /**
     * Look up a template image by file name
     * Include template 0 as it is used for logo images etc.
     *
     * @param int    $templateId the template id
     * @param string $filename   the file name to look-up
     *
     * @return array|false
     */
    public function templateImage($templateId, $filename)
    {
        $filename = sql_escape($filename);
        $sql =
            "SELECT id, data, mimetype, width, height
            FROM {$this->tables['templateimage']}
            WHERE (template = $templateId OR template = 0) AND filename = '$filename'";

        return $this->dbCommand->queryRow($sql);
    }

    public function templateImageById($imageId)
    {
        $sql =
            "SELECT data, mimetype, width, height
            FROM {$this->tables['templateimage']}
            WHERE id = $imageId";

        return $this->dbCommand->queryRow($sql);
    }

    public function attachments($mid)
    {
        $sql =
            "SELECT a.id, filename, remotefile, mimetype, description, size
            FROM {$this->tables['attachment']} a
            JOIN {$this->tables['message_attachment']} ma ON a.id = ma.attachmentid
            WHERE ma.messageid = $mid";

        return $this->dbCommand->queryAll($sql);
    }

    public function getUserAttributeValues($email)
    {
        return getUserAttributeValues($email);
    }

    public function loadMessageData($mid)
    {
        return loadMessageData($mid);
    }

    public function fetchUrl($url, $user)
    {
        return fetchUrl($url, $user);
    }
}
