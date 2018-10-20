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

use phpList\plugin\Common\DAO as CommonDAO;
use phpList\plugin\Common\DAO\ListsTrait;
use phpList\plugin\Common\DAO\UserTrait;

/**
 * DAO class providing access to the message table.
 */
class DAO extends CommonDAO
{
    use ListsTrait;
    use UserTrait;

    public function forwardId($url)
    {
        $url = sql_escape($url);
        $sql =
            "SELECT id
            FROM {$this->tables['linktrack_forward']} AS ltf
            WHERE ltf.url = '$url'";

        return $this->dbCommand->queryOne($sql);
    }

    public function forwardUuid($url)
    {
        $url = sql_escape($url);
        $sql =
            "SELECT uuid
            FROM {$this->tables['linktrack_forward']} AS ltf
            WHERE ltf.url = '$url'";

        return $this->dbCommand->queryOne($sql);
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

    public function messagesForUser($uid, $start = null, $limit = null)
    {
        $range = $start === null ? '' : "LIMIT $start, $limit";
        $sql = <<<END
            SELECT m.subject, um.messageid, DATE(um.entered) AS entered
            FROM {$this->tables['message']} m
            JOIN {$this->tables['usermessage']} um ON um.messageid = m.id
            JOIN {$this->tables['user']} u ON u.id = um.userid
            WHERE u.uniqid = '$uid'
            AND um.status = 'sent'
            ORDER BY um.entered DESC
            $range
END;

        return $this->dbCommand->queryAll($sql);
    }

    public function totalMessagesForUser($uid)
    {
        $sql = <<<END
            SELECT COUNT(*)
            FROM {$this->tables['message']} m
            JOIN {$this->tables['usermessage']} um ON um.messageid = m.id
            JOIN {$this->tables['user']} u ON u.id = um.userid
            WHERE u.uniqid = '$uid'
            AND um.status = 'sent'
END;

        return $this->dbCommand->queryOne($sql);
    }

    public function subscriberForAdmin($adminId)
    {
        $sql = <<<END
            SELECT u.uniqid, u.email
            FROM {$this->tables['admin']} a
            JOIN {$this->tables['user']} u ON u.email = a.email
            WHERE a.id = $adminId
END;

        return $this->dbCommand->queryRow($sql);
    }

    public function messagesForList($listId, $start = null, $limit = null)
    {
        $range = $start === null ? '' : "LIMIT $start, $limit";
        $sql = <<<END
            SELECT m.subject, m.id as messageid, DATE(m.sent) AS entered
            FROM {$this->tables['message']} m
            JOIN {$this->tables['listmessage']} lm ON lm.messageid = m.id
            WHERE lm.listid = $listId
            AND m.status = 'sent'
            ORDER BY m.sent DESC
            $range
END;

        return $this->dbCommand->queryAll($sql);
    }

    public function totalMessagesForList($listId)
    {
        $sql = <<<END
            SELECT COUNT(*)
            FROM {$this->tables['message']} m
            JOIN {$this->tables['listmessage']} lm ON lm.messageid = m.id
            WHERE lm.listid = $listId
            AND m.status = 'sent'
END;

        return $this->dbCommand->queryOne($sql);
    }

    public function wasUserSentMessage($mid, $uid)
    {
        $sql = <<<END
            SELECT EXISTS (
                SELECT 1
                FROM {$this->tables['usermessage']} um
                JOIN {$this->tables['user']} u ON u.id = um.userid
                WHERE um.messageid = $mid AND u.uniqid = '$uid'
            )
END;

        return $this->dbCommand->queryOne($sql);
    }
}
