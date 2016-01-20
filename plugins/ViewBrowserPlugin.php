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
 * @copyright 2014-2015 Duncan Cameron
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
    const VIEW_FILE = 'view.php';
    const PUBLIC_PAGE_VERSION = '3.0.7';
    const LOGO_VERSION = '3.2.2';

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

    /*
     * Public functions
     */

    public function dependencyCheck()
    {
        global $plugins;

        return array(
            'XSL extension installed' => extension_loaded('xsl'),
            'Common Plugin v3.0.2 or later installed' => phpListPlugin::isEnabled('CommonPlugin')
                    && preg_match('/\d+\.\d+\.\d+/', $plugins['CommonPlugin']->version, $matches)
                    && version_compare($matches[0], '3.0.2') >= 0,
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
            'PHP version 5.3.0 or greater' => version_compare(PHP_VERSION, '5.3') > 0,
        );
    }

    public function adminmenu()
    {
        return array();
    }

    public function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/' . self::PLUGIN . '/';
        $this->settings = array(
            'viewbrowser_link' => array(
              'value' => s('View in browser'),
              'description' => s('The text of the link'),
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
              'value' => "ContentAreas\nconditionalPlaceholderPlugin\nRssFeedPlugin\nViewBrowserPlugin",
              'allowempty' => true,
              'category' => 'View in Browser',
            ),
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
        $this->rootUrl = sprintf('%s://%s%s/', $public_scheme, getConfig('website'), $pageroot);
    }

    /*
     *  Replace placeholders in html message
     *
     */
    public function parseOutgoingHTMLMessage($messageid, $content, $destination, $userdata = null)
    {
        $url = $this->viewUrl($messageid, $userdata['uniqid']);
        $attributes = stripslashes(getConfig('viewbrowser_attributes'));

        return str_ireplace(
            array('[VIEWBROWSER]', '[VIEWBROWSERURL]'),
            array($this->link($this->linkText, $url, $attributes), htmlspecialchars($url)),
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
