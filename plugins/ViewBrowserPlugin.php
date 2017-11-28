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

/**
 * Registers the plugin with phplist.
 */
class ViewBrowserPlugin extends phplistPlugin
{
    const VERSION_FILE = 'version.txt';
    const PLUGIN = 'ViewBrowserPlugin';
    const VIEW_PAGE = 'view';
    const IMAGE_PAGE = 'image';
    const ARCHIVE_PAGE = 'archive';
    const ADMIN_ARCHIVE_PAGE = 'adminarchive';
    const VIEW_FILE = 'view.php';
    const PUBLIC_PAGE_VERSION = '3.0.7';
    const LOGO_VERSION = '3.2.2';
    const TRACKID_VERSION = '3.3';

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
    public $topMenuLinks = array(
        self::ADMIN_ARCHIVE_PAGE => array('category' => 'campaigns'),
    );
    public $publicPages = array(self::VIEW_PAGE, self::IMAGE_PAGE, self::ARCHIVE_PAGE);
    public $documentationUrl = 'https://resources.phplist.com/plugin/viewinbrowser';

    /*
     * Private functions
     */
    private function archiveUrl($uid)
    {
        $params = array(
            'p' => self::ARCHIVE_PAGE,
            'pi' => self::PLUGIN,
            'uid' => $uid,
        );

        return $this->rootUrl . '?' . http_build_query($params, '', '&');
    }

    private function viewUrl($messageid, $uid)
    {
        $params = array('m' => $messageid);

        if ($uid) {
            $params['uid'] = $uid;
        }

        $url = $this->rootUrl;

        if (version_compare(getConfig('version'), self::PUBLIC_PAGE_VERSION) < 0) {
            $url .= self::VIEW_FILE;
        } else {
            $params['p'] = self::VIEW_PAGE;
            $params['pi'] = self::PLUGIN;
        }

        return $url . '?' . http_build_query($params, '', '&');
    }

    private function link($linkText, $url, $attributes)
    {
        return sprintf('<a href="%s" %s>%s</a>', htmlspecialchars($url), $attributes, htmlspecialchars($linkText));
    }

    /**
     * Remove placeholders.
     *
     * @param string $content the message content
     *
     * @return string content with placeholders removed
     */
    private function removePlaceholders($content)
    {
        $content = preg_replace(
            ['/\[VIEWBROWSER(?::(\d+))?]/i', '/\[VIEWBROWSERURL(?::(\d+))?]/i'],
            '',
            $content
        );
        $content = str_ireplace(
            ['[ARCHIVE]', '[ARCHIVEURL]'],
            '',
            $content
        );

        return $content;
    }

    /**
     * Replace placeholders.
     *
     * @param string   $content          the message content
     * @param callable $viewLinkCallback replacement callback for the view link
     * @param callable $viewUrlCallback  replacement callback for the view url
     * @param string   $archiveLink      replacement text for the archive link
     * @param string   $archiveUrl       replacement text for the archive url
     *
     * @return string content with placeholders replaced
     */
    private function replacePlaceholders($content, $viewLinkCallback, $viewUrlCallback, $archiveLink, $archiveUrl)
    {
        $content = preg_replace_callback(
            '/\[VIEWBROWSER(?::(\d+))?]/i',
            $viewLinkCallback,
            $content
        );
        $content = preg_replace_callback(
            '/\[VIEWBROWSERURL(?::(\d+))?]/i',
            $viewUrlCallback,
            $content
        );
        $content = str_ireplace(
            ['[ARCHIVE]', '[ARCHIVEURL]'],
            [$archiveLink, $archiveUrl],
            $content
        );

        return $content;
    }

    /*
     * Public functions
     */

    public function dependencyCheck()
    {
        global $plugins;

        return array(
            'XSL extension installed' => extension_loaded('xsl'),
            'Common Plugin v3.6.3 or later installed' => (
                phpListPlugin::isEnabled('CommonPlugin')
                && version_compare($plugins['CommonPlugin']->version, '3.6.3') >= 0
            ),
            'RSS Feed plugin v2.2.0 or later installed' => (
                phpListPlugin::isEnabled('RssFeedPlugin')
                && version_compare($plugins['RssFeedPlugin']->version, '2.2.0') >= 0
                || !phpListPlugin::isEnabled('RssFeedPlugin')
            ),
            'Content Areas plugin v1.4.0 or later installed' => (
                phpListPlugin::isEnabled('ContentAreas')
                && version_compare($plugins['ContentAreas']->version, '1.4.0') >= 0
                || !phpListPlugin::isEnabled('ContentAreas')
            ),
            'PHP version 5.4 or greater' => version_compare(PHP_VERSION, '5.4') > 0,
        );
    }

    public function adminmenu()
    {
        return array();
    }

    public function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/' . self::PLUGIN . '/';
        $styles = <<<'END'
#archive {
    line-height: 150%;
    font-family: Helvetica;
    font-size: 14px;
    color: #333333;
}
#archive-list {
    display: block;
    margin: 15px 0;
    padding: 0;
    border-top: 1px solid #eee;
}
#archive-list li {
    display: block;
    list-style: none;
    margin: 0;
    padding: 6px 10px;
    border-bottom: 1px solid #aaa;
    line-height: 150%;
    font-family: Helvetica;
    font-size: 14px;
    color: #333333;
}
.content #archive .campaign-id {
    display: none;
}
END;
        $this->settings = array(
            'viewbrowser_link' => array(
                'value' => s('View in browser'),
                'description' => s('The text of the link'),
                'type' => 'text',
                'allowempty' => false,
                'category' => 'View in Browser',
            ),
            'viewbrowser_archive_link' => array(
                'value' => s('email archive'),
                'description' => s('The text of the archive link'),
                'type' => 'text',
                'allowempty' => false,
                'category' => 'View in Browser',
            ),
            'viewbrowser_attributes' => array(
                'value' => s(''),
                'description' => s('Additional attributes for the html &lt;a> element'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'View in Browser',
            ),
            'viewbrowser_anonymous' => array(
                'value' => false,
                'description' => s('Whether the plugin should provide an anonymous page'),
                'type' => 'boolean',
                'allowempty' => false,
                'category' => 'View in Browser',
            ),
            'viewbrowser_plugins' => array(
                'description' => s('Plugins to be used when creating the email. Usually leave this unchanged.'),
                'type' => 'textarea',
                'value' => "ContentAreas\nconditionalPlaceholderPlugin\nRssFeedPlugin\nViewBrowserPlugin\nSubscribersPlugin",
                'allowempty' => true,
                'category' => 'View in Browser',
            ),
            'viewbrowser_archive_styles' => array(
                'value' => $styles,
                'description' => s('CSS to be applied to the campaign archive page'),
                'type' => 'textarea',
                'allowempty' => false,
                'category' => 'View in Browser',
            ),
        );
        $this->pageTitles = array(
            self::ADMIN_ARCHIVE_PAGE => s('Campaign archive'),
        );
        parent::__construct();
        $this->version = (is_file($f = $this->coderoot . self::VERSION_FILE))
            ? file_get_contents($f)
            : '';
    }

    public function activate()
    {
        global $public_scheme, $pageroot;

        parent::activate();
        $this->linkText = getConfig('viewbrowser_link');
        $this->archiveLinkText = getConfig('viewbrowser_archive_link');
        $this->rootUrl = sprintf('%s://%s%s/', $public_scheme, getConfig('website'), $pageroot);
    }

    /**
     * Replace placeholders in HTML format message.
     * When a message is being forwarded and anonymous page is not enabled then remove the placeholders.
     *
     * @param int    $messageid   the message id
     * @param string $content     the message content
     * @param string $destination the destination email address
     * @param array  $userdata    the user data values
     *
     * @return string content with placeholders replaced
     */
    public function parseOutgoingHTMLMessage($messageid, $content, $destination, $userdata = null)
    {
        if (empty($userdata['uniqid']) && !getConfig('viewbrowser_anonymous')) {
            return $this->removePlaceholders($content);
        }
        $uniqid = isset($userdata['uniqid']) ? $userdata['uniqid'] : '';
        $archiveUrl = $this->archiveUrl($uniqid);
        $attributes = stripslashes(getConfig('viewbrowser_attributes'));

        $viewLinkCallback = function (array $matches) use ($messageid, $uniqid, $attributes) {
            $mid = count($matches) > 1 ? $matches[1] : $messageid;
            $url = $this->viewUrl($mid, $uniqid);

            return $this->link($this->linkText, $url, $attributes);
        };

        $viewUrlCallback = function (array $matches) use ($messageid, $uniqid) {
            $mid = count($matches) > 1 ? $matches[1] : $messageid;
            $url = $this->viewUrl($mid, $uniqid);

            return htmlspecialchars($url);
        };

        return $this->replacePlaceholders(
            $content,
            $viewLinkCallback,
            $viewUrlCallback,
            $this->link($this->archiveLinkText, $archiveUrl, $attributes),
            htmlspecialchars($archiveUrl)
        );
    }

    /**
     * Replace placeholders in text format message.
     * When a message is being forwarded and anonymous page is not enabled then remove the placeholders.
     *
     * @param int    $messageid   the message id
     * @param string $content     the message content
     * @param string $destination the destination email address
     * @param array  $userdata    the user data values
     *
     * @return string content with placeholders replaced
     */
    public function parseOutgoingTextMessage($messageid, $content, $destination, $userdata = null)
    {
        if (empty($userdata['uniqid']) && !getConfig('viewbrowser_anonymous')) {
            return $this->removePlaceholders($content);
        }
        $uniqid = isset($userdata['uniqid']) ? $userdata['uniqid'] : '';
        $url = $this->viewUrl($messageid, $uniqid);
        $archiveUrl = $this->archiveUrl($uniqid);

        $viewLinkCallback = function (array $matches) use ($messageid, $uniqid) {
            $mid = count($matches) > 1 ? $matches[1] : $messageid;
            $url = $this->viewUrl($mid, $uniqid);

            return "$this->linkText $url";
        };

        $viewUrlCallback = function (array $matches) use ($messageid, $uniqid) {
            $mid = count($matches) > 1 ? $matches[1] : $messageid;
            $url = $this->viewUrl($mid, $uniqid);

            return $url;
        };

        return $this->replacePlaceholders(
            $content,
            $viewLinkCallback,
            $viewUrlCallback,
            "$this->archiveLinkText $archiveUrl",
            $archiveUrl
        );
    }
}
