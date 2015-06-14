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
 * @copyright 2015 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * Class to create the content of a campaign email
 * 
 */
class ViewBrowserPlugin_ContentCreator
{
    /*
     *  Private variables
     */
    private $rootUrl;
    private $dao;

    /*
     * Private functions
     */
    private function replaceSignature($content, $signature)
    {
        $content = str_ireplace('[SIGNATURE]', $signature, $content, $count);

        if ($count == 0 && $signature) {
            $content = addHTMLFooter($content, $signature);
        }
        return $content;
    }

    private function replaceFooter($content, $footer)
    {
        $content = str_ireplace('[FOOTER]', $footer, $content, $count);

        if ($count == 0 && $footer) {
            $content = addHTMLFooter($content, '<br />' . $footer);
        }
        return $content;
    }

    private function replaceUserTrack($content, $mid, $uid)
    {
        $image = sprintf(
            '<img src="%sut.php?u=%s&amp;m=%d" width="1" height="1" border="0" />',
            $this->rootUrl, $uid, $mid
        );

        $content = preg_replace('/\[USERTRACK]/i', $image, $content, 1, $count);

        if ($count == 0) {
            if (ALWAYS_ADD_USERTRACK) {
                $content = addHTMLFooter($content, $image);
            }
        } else {
            $content = str_ireplace('[USERTRACK]', '', $content);
        }
        return $content;
    }

    private function systemPlaceholders($uid, $email, $message)
    {
        global $website, $domain, $strUnsubscribe, $strThisLink, $strForward;

        $messageid = $message['id'];
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

        $p["messageid"] = $messageid;
        $p['website'] = $website;
        $p['domain'] = $domain;
        $p['subject'] = $message['subject'];
        $p['fromemail'] = $message['fromemail'];
        return $p;
    }

    /*
     * Public functions
     */
    public function __construct()
    {
        global $public_scheme, $pageroot;

        $this->rootUrl = sprintf('%s://%s%s/', $public_scheme, getConfig('website'), $pageroot);
    }

    public function createContent($mid, $uid)
    {
        global $PoweredByText, $PoweredByImage, $plugins;

        $this->dao = new ViewBrowserPlugin_DAO(new CommonPlugin_DB());
        $row = $this->dao->message($mid);

        if (!$row) {
            return s('Message with id %d does not exist', $mid);
        }
        $personalise = ($uid !== '');

        if ($personalise) {
            $user = $this->dao->userByUniqid($uid);

            if (!$user) {
                return s('User with uid %s does not exist', $uid);
            }
            $attributeValues = getUserAttributeValues($user['email']);
        } else {
            $user = array('email' => '', 'uniqid' => '');
            $daoAttr = new CommonPlugin_DAO_Attribute(new CommonPlugin_DB());
            $attributeValues = array();

            foreach ($daoAttr->attributes() as $k => $v) {
                $attributeValues[$v['name']] = '';
            }
        }

        $message = loadMessageData($mid);

        if ($message['sendmethod'] == 'remoteurl') {
            $content = fetchUrl($message['sendurl'], $user);

            if (!$content) {
                return s('Unable to retrieve URL %s', $message['sendurl']);
            }
            $template = 0;
        } else {
            $content = $message['message'];
            $template = $row['template'];

            if ($template) {
                $template = str_replace('\"', '"', $template);
                $content = str_ireplace('[CONTENT]', $content, $template);
            }
        }
        $content = $this->replaceFooter($content, $message['footer']);
        $content = $this->replaceSignature($content, EMAILTEXTCREDITS ? $PoweredByText : $PoweredByImage);

        $content = parsePlaceHolders($content, $user);
        $content = parsePlaceHolders($content, $attributeValues);
        $content = parsePlaceHolders($content, $this->systemPlaceholders($uid, $user['email'], $message));
        $content = $this->replaceUserTrack($content, $mid, $uid);

        $destinationEmail = $user['email'];

        foreach ($plugins as $plugin) {
            $destinationEmail = $plugin->setFinalDestinationEmail($mid, $attributeValues, $destinationEmail);
        }

        foreach ($plugins as $plugin) {
            $content = $plugin->parseOutgoingHTMLMessage($mid, $content, $destinationEmail, $user);
        }
        $doc = new ViewBrowserPlugin_ContentDocument($content, $this->dao, $this->rootUrl);
        $doc->addTemplateImages($mid, $message['template']);

        if (CLICKTRACK && $personalise) {
            $doc->addLinkTrack($mid, $user);
        }
        $styles = $template ? '' : trim(getConfig("html_email_style"));
        $doc->addTitle($message['subject'], $styles);
        return $doc->toHtml();
    }

}
