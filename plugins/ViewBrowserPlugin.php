<?php
/**
 * ViewBrowserPlugin for phplist
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
 * @package   ViewBrowserPlugin
 * @author    Duncan Cameron
 * @copyright 2014 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

 /**
 * Registers the plugin with phplist
 */

class ViewBrowserPlugin extends phplistPlugin
{
    const VERSION_FILE = 'version.txt';
    const CODE_DIR = '/ViewBrowserPlugin/';

    /*
     *  Private variables
     */
    private $personalise;
    private $linkText;
    private $url;

    /*
     *  Inherited variables
     */
    public $name = 'View in Browser plugin';
    public $authors = 'Duncan Cameron';
    public $enabled = 1;
    public $settings = array(
        'viewbrowser_personalise' => array (
          'value' => true,
          'description' => 'personalise the email',
          'type' => 'boolean',
          'allowempty' => true,
          'category'=> 'View in Browser',
        ),
        'viewbrowser_link' => array (
          'value' => 'View in browser',
          'description' => 'The text of the link',
          'type' => 'text',
          'allowempty' => false,
          'category'=> 'View in Browser',
        )
    );
    /*
     * Private functions
     */
    private function replacePlaceholder($content, $messageid, $uid)
    {
        $params = array('m' => $messageid);

        if ($this->personalise && $uid) {
            $params['uid'] = $uid;
        }

        $link = sprintf('<a href="%s">%s</a>', htmlspecialchars($this->url . http_build_query($params)), $this->linkText);
        return str_ireplace('[VIEWBROWSER]', $link, $content);
    }

    private function systemPlaceholders($uid, $email, $messageid)
    {
        global $strUnsubscribe, $strThisLink, $strForward;

        $p = array();
        $url = getConfig("unsubscribeurl");
        $sep = strpos($url, '?') === false ? '?':'&';

        $p["unsubscribeurl"] = sprintf('%s%suid=%s',$url, htmlspecialchars($sep), $uid);
        $p["unsubscribe"] = sprintf('<a href="%s">%s</a>' ,$p["unsubscribeurl"], $strUnsubscribe);

        $url = getConfig("blacklisturl");
        $sep = strpos($url, '?') === false ? '?':'&';
        $p["blacklisturl"] = sprintf('%s%semail=%s',$url,htmlspecialchars($sep),$email);
        $p["blacklist"] = sprintf('<a href="%s">%s</a>', $p["blacklisturl"], $strUnsubscribe);

        $url = getConfig("subscribeurl");
        $p["subscribeurl"] = $url;
        $p["subscribe"] = sprintf('<a href="%s">%s</a>', $url, $strThisLink);

        $url = getConfig("forwardurl");
        $sep = strpos($url, '?') === false ? '?':'&';
        $p["forwardurl"] = sprintf('%s%suid=%s&amp;mid=%d', $url, htmlspecialchars($sep), $uid, $messageid);
        $p["forward"] = sprintf('<a href="%s">%s</a>', $p["forwardurl"], $strThisLink);
        $p["messageid"] = $messageid;

        $url = getConfig("forwardurl");
        $p["forwardform"] = sprintf(
            '<form method="get" action="%s" name="forwardform" class="forwardform">
                <input type="hidden" name="uid" value="%s" />
                <input type="hidden" name="mid" value="%d" />
                <input type="hidden" name="p" value="forward" />
                <input type=text name="email" value="" class="forwardinput" />
                <input name="Send" type="submit" value="%s" class="forwardsubmit"/>
            </form>',
            $url, $uid, $messageid, $strForward
        );

        $url = getConfig("preferencesurl");
        $sep = strpos($url,'?') === false ? '?':'&';
        $p["preferencesurl"] = sprintf('%s%suid=%s',$url,htmlspecialchars($sep),$uid);
        $p["preferences"] = sprintf('<a href="%s">%s</a>', $p["preferencesurl"], $strThisLink);

        $url = getConfig("confirmationurl");
        $sep = strpos($url,'?') === false ? '?':'&';
        $p["confirmationurl"] = sprintf('%s%suid=%s', $url, htmlspecialchars($sep), $uid);

        $p['website'] = $GLOBALS['website'];
        $p['domain'] = $GLOBALS['domain'];
        return $p;
    }

    private function replaceUserTrack($content, $mid, $uid)
    {
        global $public_scheme, $pageroot;

        $content = preg_replace(
            '/\[USERTRACK]/i',
            sprintf(
                '<img src="%s://%s%s/ut.php?u=%s&amp;m=%d" width="1" height="1" border="0" />',
                $public_scheme, getConfig('website'), $pageroot, $uid, $mid
            ),
            $content,
            1
        );
        return str_ireplace('[USERTRACK]', '', $content);
    }

    private function addHead($message, $title, $styles)
    {
        $title = htmlspecialchars($title);
        $doc = new DOMDocument;
        $doc->loadHTML($message);
        $xsl = new DOMDocument;
        $xsl->loadXML(<<<END
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <!-- identity transformation -->
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>

    <!-- match html that does not have a head element -->
    <xsl:template match="//*[local-name()='html' and not(head)]">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
    <head>
        <title>$title</title>
        $styles
    </head>
            <xsl:apply-templates select="node()"/>
        </xsl:copy>
    </xsl:template>

<!-- match head that does not have a title element -->
    <xsl:template match="head[not(title)]">
        <xsl:copy>
        <title>$title</title>
            <xsl:apply-templates select="node()"/>
        </xsl:copy>
    </xsl:template>

<!-- match title element -->
    <xsl:template match="head/title">
        <title>$title</title>
    </xsl:template>

</xsl:stylesheet>
END
    );
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl);

        return $proc->transformToXML($doc);

    }
    /*
     * Public functions
     */
    public function adminmenu()
    {
        return array();
    }
 
    public function __construct()
    {
        global $pageroot;

        $this->coderoot = dirname(__FILE__) . '/ViewBrowserPlugin/';
        $this->version = (is_file($f = $this->coderoot . self::VERSION_FILE))
            ? file_get_contents($f)
            : '';
        parent::__construct();

        $this->personalise = getConfig('viewbrowser_personalise');
        $this->linkText = htmlspecialchars(getConfig('viewbrowser_link'));
        $this->url = sprintf('http://%s%s/view.php?', getConfig('website'), $pageroot);
    }

    public function createEmail($mid, $uid)
    {
        $dao = new ViewBrowserPlugin_DAO(new CommonPlugin_DB());
        $row = $dao->messageData($mid);

        if (!$row) {
            return "Message with id $mid does not exist";
        }
        $subject = $row['subject'];
        $message = $row['message'];
        $template = $row['template'];

        if ($template) {
            $template = str_replace('\"', '"', $template);
            $message = str_ireplace('[CONTENT]', $message, $template);
        }

        if ($uid && ($user = $dao->userByUniqid($uid))) {
            $attributeValues = getUserAttributeValues($user['email']);
            $message = parsePlaceHolders($message, $user);
            $message = parsePlaceHolders($message, $attributeValues);
            $message = parsePlaceHolders($message, $this->systemPlaceholders($uid, $user['email'], $mid));
        }
        $message = $this->replacePlaceholder($message, $mid, $uid);
        $message = $this->replaceUserTrack($message, $mid, $uid);

        $styles = $template ? '' : trim(getConfig("html_email_style"));
        $message = $this->addHead($message, $subject, $styles);

        return $message;
    }

    /*
     *  Replace placeholder in html message
     *
     */
    public function parseOutgoingHTMLMessage($messageid, $content, $destination, $userdata)
    {
        return $this->replacePlaceholder($content, $messageid, $userdata['uniqid']);
    }

    /*
     *  Replace placeholder in text message
     *
     */
    public function parseOutgoingTextMessage($messageid, $content, $destination, $userdata)
    {
        return $this->replacePlaceholder($content, $messageid, $userdata['uniqid']);
    }
}
