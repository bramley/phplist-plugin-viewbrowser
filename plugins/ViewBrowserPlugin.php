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
    const PLUGIN = 'ViewBrowserPlugin';
    const VIEW_PAGE = 'view';
    const IMAGE_PAGE = 'image';
    const VIEW_FILE = 'view.php';
    const PHPLIST_VERSION = '3.0.7';

    /*
     *  Private variables
     */
    private $linkText;
    private $rootUrl;
    private $dao;

    /*
     *  Inherited variables
     */
    public $name = 'View in Browser plugin';
    public $authors = 'Duncan Cameron';
    public $enabled = 1;
    public $settings;
    public $dependencyCheck = array(
        'phpList version newer than 3.0.10' => 'VERSION > "3.0.10"',
        'XSL extension available' => 'extension_loaded("xsl")',
    );
    public $publicPages = array(self::VIEW_PAGE, self::IMAGE_PAGE);

    /*
     * Private functions
     */
    private function viewUrl($messageid, $uid)
    {
        $params = array('m' => $messageid);

        if ($uid) {
            $params['uid'] = $uid;
        }

        $url = $this->rootUrl . '/';

        if (version_compare(getConfig('version'), self::PHPLIST_VERSION) < 0) {
            $url .= self::VIEW_FILE;
        } else {
            $params['p'] = self::VIEW_PAGE;
            $params['pi'] = self::PLUGIN;
        }
        return $url . '?' . http_build_query($params, '', '&');
    }

    private function link($linkText, $url)
    {
        return sprintf('<a href="%s">%s</a>', htmlspecialchars($url), htmlspecialchars($linkText));
    }

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
            '<img src="%s/ut.php?u=%s&amp;m=%d" width="1" height="1" border="0" />',
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

    private function addLinkTrack(DOMDocument $dom, $mid, array $user)
    {
        $linkTrackUrl = $this->rootUrl . '/lt.php?id=';
        $nodes = $dom->getElementsByTagName('a');

        foreach ($nodes as $node) {
            $text = $node->textContent;
            $href = $node->getAttribute('href');

            if (stripos($text, 'http') === 0 || stripos($href, 'www.phplist.com') !== false
                || stripos($href, $linkTrackUrl) !== false ) {
                continue;
            }

            $url = cleanUrl($href, array('PHPSESSID', 'uid'));
            $linkid = $this->dao->forwardId($url);

            if ($linkid) {
                $masked = "H|$linkid|$mid|" . $user['id'] ^ XORmask;
                $masked = urlencode(base64_encode($masked));
                $node->setAttribute('href', $linkTrackUrl . $masked);
            }
        }
    }

    private function addTemplateImages(DOMDocument $dom, $messageId, $templateId)
    {
        foreach ($dom->getElementsByTagName('img') as $element) {
            $src = $element->getAttribute('src');

            if ($row = $this->dao->templateImage($templateId, $src)) {
                if (version_compare(getConfig('version'), self::PHPLIST_VERSION) < 0) {
                    $data = "data:{$row['mimetype']};base64," . $row['data'];
                } else {
                    $data = $this->rootUrl . '/?' . http_build_query(
                        array('pi' => self::PLUGIN, 'p' => self::IMAGE_PAGE, 'id' => $row['id']), '', '&'
                    );
                }
                $element->setAttribute('src', $data);
            }
        }
    }

    private function toHtml(DOMDocument $doc, DOMDocumentType $docType)
    {
        if ($docType && $docType->publicId && $docType->systemId) {
            $public = "doctype-public=\"$docType->publicId\"";
            $system = "doctype-system=\"$docType->systemId\"";
            $documentType = '';
        } else {
            $public = $system = '';
            $documentType = '&lt;!DOCTYPE html>&#x0A;';
        }
        $ss = <<<END
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8" $public $system />
    <!-- identity transformation -->
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>

    <!-- start output from html element -->
    <xsl:template match="/">
        <xsl:text disable-output-escaping="yes">$documentType</xsl:text>
        <xsl:apply-templates select="html"/>
    </xsl:template>

</xsl:stylesheet>
END;
        $xsl = new DOMDocument;
        $xsl->loadXML($ss);
        $proc = new XSLTProcessor;
        $proc->importStylesheet($xsl);
        return $proc->transformToXML($doc);
    }

    private function transform(DOMDocument $doc, $title, $styles)
    {
        $title = htmlspecialchars($title);
        $xsl = new DOMDocument;
        $ss = <<<END
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
END;
        $xsl->loadXML($ss);
        $proc = new XSLTProcessor;
        $proc->importStylesheet($xsl);
        return $proc->transformToDoc($doc);
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
        global $public_scheme, $pageroot;

        $this->coderoot = dirname(__FILE__) . '/' . self::PLUGIN . '/';
        $this->version = (is_file($f = $this->coderoot . self::VERSION_FILE))
            ? file_get_contents($f)
            : '';
        $this->settings = array(
            'viewbrowser_link' => array (
              'value' => s('View in browser'),
              'description' => s('The text of the link'),
              'type' => 'text',
              'allowempty' => false,
              'category'=> 'View in Browser',
            ),
            'viewbrowser_anonymous' => array (
              'value' => false,
              'description' => s('Whether the plugin should provide an anonymous page'),
              'type' => 'boolean',
              'allowempty' => false,
              'category'=> 'View in Browser',
            )
        );
        parent::__construct();

        $this->linkText = getConfig('viewbrowser_link');
        $this->rootUrl = sprintf('%s://%s%s', $public_scheme, getConfig('website'), $pageroot);
    }

    public function createEmail($mid, $uid)
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

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->encoding = 'UTF-8';
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $this->addTemplateImages($dom, $mid, $message['template']);

        if (CLICKTRACK && $personalise) {
            $this->addLinkTrack($dom, $mid, $user);
        }
        $docType = $dom->doctype;
        $styles = $template ? '' : trim(getConfig("html_email_style"));
        $dom = $this->transform($dom, $message['subject'], $styles);
        return $this->toHtml($dom, $docType);
    }

    /*
     *  Replace placeholders in html message
     *
     */
    public function parseOutgoingHTMLMessage($messageid, $content, $destination, $userdata = null)
    {
        $url = $this->viewUrl($messageid, $userdata['uniqid']);

        return str_ireplace(
            array('[VIEWBROWSER]', '[VIEWBROWSERURL]'),
            array($this->link($this->linkText, $url), htmlspecialchars($url)),
            $content
        );
    }

    /*
     *  Replace placeholders in text message
     *
     */
    public function parseOutgoingTextMessage($messageid, $content, $destination, $userdata = null)
    {
        $url = $this->viewUrl($messageid, $userdata['uniqid']);

        return str_ireplace(
            array('[VIEWBROWSER]', '[VIEWBROWSERURL]'),
            array("$this->linkText $url", $url),
            $content
        );
    }
}
