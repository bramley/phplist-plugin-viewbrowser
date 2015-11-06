<?php

namespace phpList\plugin\ViewBrowserPlugin;

use DomDocument;
use XSLTProcessor;

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
 * Class to manipulate the content as an HTML DOM document.
 */
class ContentDocument
{
    private $dom;
    private $docType;
    private $dao;
    private $rootUrl;

    public function __construct($content, $dao, $rootUrl)
    {
        $this->dao = $dao;
        $this->rootUrl = $rootUrl;
        libxml_use_internal_errors(true);
        $this->dom = new DOMDocument();
        $this->dom->encoding = 'UTF-8';
        $this->dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $this->docType = $this->dom->doctype;
    }

    public function addTemplateImages($messageId, $templateId)
    {
        foreach ($this->dom->getElementsByTagName('img') as $element) {
            $src = $element->getAttribute('src');

            if ($row = $this->dao->templateImage($templateId, $src)) {
                if (version_compare(getConfig('version'), \ViewBrowserPlugin::PUBLIC_PAGE_VERSION) < 0) {
                    $data = "data:{$row['mimetype']};base64," . $row['data'];
                } else {
                    $data = $this->rootUrl . '?' . http_build_query(
                        array('pi' => \ViewBrowserPlugin::PLUGIN, 'p' => \ViewBrowserPlugin::IMAGE_PAGE, 'id' => $row['id']), '', '&'
                    );
                }
                $element->setAttribute('src', $data);
            }
        }
    }

    public function addLinkTrack($mid, array $user)
    {
        $linkTrackUrl = $this->rootUrl . 'lt.php?id=';
        $nodes = $this->dom->getElementsByTagName('a');

        foreach ($nodes as $node) {
            $text = $node->textContent;
            $href = $node->getAttribute('href');

            if (stripos($text, 'http') === 0 || stripos($href, 'www.phplist.com') !== false
                || stripos($href, $linkTrackUrl) !== false) {
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

    public function toHtml()
    {
        if ($this->docType && $this->docType->publicId && $this->docType->systemId) {
            $public = 'doctype-public="' . $this->docType->publicId . '"';
            $system = 'doctype-system="' . $this->docType->systemId . '"';
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
        $xsl = new DOMDocument();
        $xsl->loadXML($ss);
        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);

        return $proc->transformToXML($this->dom);
    }

    public function addTitle($title, $styles)
    {
        $title = htmlspecialchars($title);
        $xsl = new DOMDocument();
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

    <!-- match elements to be removed  -->
    <xsl:template match="*[@class='viewbrowser']">
    </xsl:template>
</xsl:stylesheet>
END;
        $xsl->loadXML($ss);
        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);
        $this->dom = $proc->transformToDoc($this->dom);
    }
}
