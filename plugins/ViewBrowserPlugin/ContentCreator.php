<?php

namespace phpList\plugin\ViewBrowserPlugin;

use phpList\plugin\Common;
use Iterator;

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
 * @copyright 2015 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * Class to create the content of a campaign email.
 */
class ContentCreator
{
    /**
     * The phplist root url.
     *
     * @var string
     */
    private $rootUrl;

    /**
     * Convert file size to appropriate unit.
     *
     * @param int $bytes    attachments
     * @param int $decimals the number of decimal places to display
     *
     * @return string file size in appropriate units
     */
    private function human_filesize($bytes, $decimals = 1)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = min(floor((strlen($bytes) - 1) / 3), count($size));

        return sprintf("%.{$decimals}f", $bytes / pow(1000, $factor)) . $size[$factor];
    }

    /**
     * Generate html to display each attachment with its download link.
     *
     * @param Iterator $attachments attachments
     *
     * @return string the html
     */
    private function addAttachments(Iterator $attachments)
    {
        $html = '<p>Attachments:<br/>';

        foreach ($attachments as $a) {
            $description = htmlspecialchars($a['description']);
            $remotefile = htmlspecialchars($a['remotefile']);
            $size = $this->human_filesize($a['size']);
            $html .= <<<END
<img src="./?p=image&amp;pi=CommonPlugin&amp;image=attach.png" alt="" title="" />
$description 
<a href="./dl.php?id={$a['id']}">$remotefile</a>
$size<br/>
END;
        }
        $html .= '</p>';

        return $html;
    }

    /**
     * Replace the signature placeholder within the content
     * If there isn't a placeholder then the signature is added to the end of the content.
     *
     * @param string $content   the current content that might contain a placeholder
     * @param string $signature the replacement value
     *
     * @return string the new content 
     */
    private function replaceSignature($content, $signature)
    {
        $content = str_ireplace('[SIGNATURE]', $signature, $content, $count);

        if ($count == 0 && $signature) {
            $content = addHTMLFooter($content, $signature);
        }

        return $content;
    }

    /**
     * Replace the footer placeholder within the content
     * If there isn't a placeholder then the footer is added to the end of the content.
     *
     * @param string $content the current content that might contain a placeholder
     * @param string $footer  the replacement value
     *
     * @return string the new content 
     */
    private function replaceFooter($content, $footer)
    {
        $content = str_ireplace('[FOOTER]', $footer, $content, $count);

        if ($count == 0 && $footer) {
            $content = addHTMLFooter($content, '<br />' . $footer);
        }

        return $content;
    }

    /**
     * Replace the usertrack placeholder within the content by a tracking image
     * If there isn't a placeholder and usertracking should always be added then the
     * image is added to the end of the content.
     *
     * @param string $content the current content that might contain a placeholder
     * @param int    $mid     the message id
     * @param int    $uid     the user unique id
     *
     * @return string the new content 
     */
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

    /**
     * Collect values for system placeholders that might need to be replaced
     * Most of the placeholder values are copied from sendemaillib.php.
     *
     * @param int    $uid     the user unique id
     * @param string $email   the email address
     * @param array  $message message fields
     *
     * @return array placeholders and values
     */
    private function systemPlaceholders($uid, $email, $message)
    {
        global $website, $domain, $strUnsubscribe, $strThisLink, $strForward;

        $messageid = $message['id'];
        $p = array();
        $url = getConfig('unsubscribeurl');
        $sep = strpos($url, '?') === false ? '?' : '&';

        $p['unsubscribeurl'] = sprintf('%s%suid=%s', $url, htmlspecialchars($sep), $uid);
        $p['unsubscribe'] = sprintf('<a href="%s">%s</a>', $p['unsubscribeurl'], $strUnsubscribe);

        $url = getConfig('blacklisturl');
        $sep = strpos($url, '?') === false ? '?' : '&';
        $p['blacklisturl'] = sprintf('%s%semail=%s', $url, htmlspecialchars($sep), $email);
        $p['blacklist'] = sprintf('<a href="%s">%s</a>', $p['blacklisturl'], $strUnsubscribe);

        $url = getConfig('subscribeurl');
        $p['subscribeurl'] = $url;
        $p['subscribe'] = sprintf('<a href="%s">%s</a>', $url, $strThisLink);

        $url = getConfig('forwardurl');
        $sep = strpos($url, '?') === false ? '?' : '&';
        $p['forwardurl'] = sprintf('%s%suid=%s&amp;mid=%d', $url, htmlspecialchars($sep), $uid, $messageid);
        $p['forward'] = sprintf('<a href="%s">%s</a>', $p['forwardurl'], $strThisLink);

        $url = getConfig('forwardurl');
        $p['forwardform'] = sprintf(
            '<form method="get" action="%s" name="forwardform" class="forwardform">
                <input type="hidden" name="uid" value="%s" />
                <input type="hidden" name="mid" value="%d" />
                <input type="hidden" name="p" value="forward" />
                <input type=text name="email" value="" class="forwardinput" />
                <input name="Send" type="submit" value="%s" class="forwardsubmit"/>
            </form>',
            $url, $uid, $messageid, $strForward
        );

        $url = getConfig('preferencesurl');
        $sep = strpos($url, '?') === false ? '?' : '&';
        $p['preferencesurl'] = sprintf('%s%suid=%s', $url, htmlspecialchars($sep), $uid);
        $p['preferences'] = sprintf('<a href="%s">%s</a>', $p['preferencesurl'], $strThisLink);

        $url = getConfig('confirmationurl');
        $sep = strpos($url, '?') === false ? '?' : '&';
        $p['confirmationurl'] = sprintf('%s%suid=%s', $url, htmlspecialchars($sep), $uid);

        $p['messageid'] = $messageid;
        $p['website'] = $website;
        $p['domain'] = $domain;
        $p['subject'] = $message['subject'];
        $p['fromemail'] = $message['fromemail'];

        return $p;
    }

    /**
     * Determines the plugins whose methods should be called when creating the email.
     * Selects those plugins that are named in the config entry.
     *
     * @return array
     */
    private function pluginsToCall()
    {
        global $plugins;

        $selectedPlugins = array_flip(preg_split("/[\r\n]+/", getConfig('viewbrowser_plugins')));

        return array_intersect_key($plugins, $selectedPlugins);
    }

    /**
     * Constructor.
     */
    public function __construct(DAO $dao = null, Common\DAO\Attribute $daoAttr = null)
    {
        global $public_scheme, $pageroot;

        $this->dao = $dao ?: new DAO(new Common\DB());
        $this->daoAttr = $daoAttr ?: new Common\DAO\Attribute(new Common\DB());
        $this->rootUrl = sprintf('%s://%s%s/', $public_scheme, getConfig('website'), $pageroot);
    }

    /**
     * Generate the html content of the email customised for the user.
     *
     * @param int     $mid             the message id
     * @param int     $uid             the user unique id
     * @param Closure $contentProvider function to provide the message content
     *
     * @return string the generated html
     */
    public function createContent($mid, $uid, \Closure $contentProvider = null)
    {
        global $PoweredByText, $PoweredByImage, $MD;

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
            $attributeValues = $this->dao->getUserAttributeValues($user['email']);
        } else {
            $user = array('email' => '', 'uniqid' => '');
            $attributeValues = array();

            foreach ($this->daoAttr->attributes() as $k => $v) {
                $attributeValues[$v['name']] = '';
            }
        }

        $callPlugins = $this->pluginsToCall();
        $message = $this->dao->loadMessageData($mid);
        $styles = '';
        $templateBody = $row['template'];

        if ($templateBody) {
            $templateBody = stripslashes($templateBody);
        }

        if ($message['sendmethod'] == 'remoteurl') {
            $content = $this->dao->fetchUrl($message['sendurl'], $user);

            if (!$content) {
                return s('Unable to retrieve URL %s', $message['sendurl']);
            }
        } else {
            foreach ($callPlugins as $plugin) {
                if (method_exists($plugin, 'viewBrowserHook')) {
                    $plugin->viewBrowserHook($templateBody, $message);
                }
            }
            $content = $message['message'];

            if ($templateBody) {
                $content = str_ireplace('[CONTENT]', $content, $templateBody);
            } else {
                $styles = trim(getConfig('html_email_style'));
            }
        }

        $content = $this->replaceFooter($content, $message['footer']);
        $content = $this->replaceSignature($content, EMAILTEXTCREDITS ? $PoweredByText : $PoweredByImage);

        $content = parsePlaceHolders($content, $user);
        $content = parsePlaceHolders($content, $attributeValues);
        $content = parsePlaceHolders($content, $this->systemPlaceholders($uid, $user['email'], $message));

        if (version_compare(getConfig('version'), \ViewBrowserPlugin::LOGO_VERSION) >= 0) {
            $content = parseLogoPlaceholders($content);
        }
        $content = $this->replaceUserTrack($content, $mid, $uid);

        if (count($attachments = $this->dao->attachments($mid)) > 0) {
            $content = addHTMLFooter($content, $this->addAttachments($attachments));
        }
        $destinationEmail = $user['email'];

        foreach ($callPlugins as $plugin) {
            $destinationEmail = $plugin->setFinalDestinationEmail($mid, $attributeValues, $destinationEmail);
        }

        foreach ($callPlugins as $plugin) {
            $content = $plugin->parseOutgoingHTMLMessage($mid, $content, $destinationEmail, $user);
        }
        $doc = new ContentDocument($content, $this->dao, $this->rootUrl);
        $doc->addTemplateImages($mid, $message['template']);

        if (CLICKTRACK && $personalise) {
            $doc->addLinkTrack($mid, $user);
        }
        $doc->addTitle($message['subject'], $styles);

        return $doc->toHtml();
    }
}
