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
use phpList\plugin\Common\DAO\MessageTrait;
use phpList\plugin\Common\DAO\TemplateTrait;
use phpList\plugin\Common\DAO\UserTrait;

/**
 * DAO class providing access to the message table.
 */
class DAO extends CommonDAO
{
    use ListsTrait;
    use MessageTrait;
    use TemplateTrait;
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
            SELECT u.uniqid, u.email, a.email AS admin_email
            FROM {$this->tables['admin']} a
            LEFT JOIN {$this->tables['user']} u ON u.email = a.email
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

    /**
     * Determine whether the user belongs to a list to which the message has been sent, not
     * necessarily having been sent the message to allow for sending a test message.
     *
     * @param int $mid the message id
     * @param int $uid the user unique id
     *
     * @return bool
     */
    public function wasUserSentMessage($mid, $uid)
    {
        $sql = <<<END
            SELECT EXISTS (
                SELECT 1
                FROM {$this->tables['listuser']} lu
                JOIN {$this->tables['listmessage']} lm ON lm.listid = lu.listid
                JOIN {$this->tables['user']} u ON u.id = lu.userid
                WHERE lm.messageid = $mid AND u.uniqid = '$uid'
            )
END;

        if ($this->dbCommand->queryOne($sql)) {
            return true;
        }
        $sql = <<<END
            SELECT EXISTS (
                SELECT 1
                FROM {$this->tables['usermessage']} um
                JOIN {$this->tables['user']} u ON u.id = um.userid
                WHERE um.messageid = $mid AND u.uniqid = '$uid'
            )
END;

        return (bool) $this->dbCommand->queryOne($sql);
    }

    /**
     * Determine whether the user is a super admin.
     *
     * @param int $uid the user unique id
     *
     * @return bool
     */
    public function isUserSuperAdmin($uid)
    {
        $sql = <<<END
            SELECT EXISTS (
                SELECT *
                FROM {$this->tables['admin']} a
                JOIN {$this->tables['user']} u ON u.email = a.email
                WHERE u.uniqid = '$uid'
                AND a.superuser = 1
                AND a.disabled = 0
            )
END;

        return (bool) $this->dbCommand->queryOne($sql);
    }
}
