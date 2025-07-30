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
 * @copyright 2014-2025 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * Class to manipulate the content as an HTML DOM document using the \Dom\HTMLDocument class.
 */

namespace phpList\plugin\ViewBrowserPlugin;

class HtmlContentDocument extends ContentDocument
{
    public function __construct($content, $dao)
    {
        $this->dom = \Dom\HTMLDocument::createFromString($content, LIBXML_NOERROR);
        $this->dao = $dao;
    }

    public function toHtml()
    {
        return $this->dom->saveHTML();
    }

    /**
     * Add title element if it does not exist, set content if the element is empty.
     * Add style element to the head element.
     *
     * @param string $title  title text
     * @param string $styles style element
     */
    public function addTitle($title, $styles)
    {
        //~ var_dump($this->dom->saveHtml());
        $headElement = $this->dom->head;
        $titleElement = $headElement->getElementsByTagName('title')->item(0);

        if ($titleElement === null) {
            $titleElement = $this->dom->createElement('title');
            $titleElement->append($title);
            $headElement->append($titleElement);
        } elseif ($titleElement->textContent === '') {
            $titleElement->replaceChildren($title);
        }

        if ($styles) {
            $fragment = $this->dom->createDocumentFragment();
            $fragment->appendXML($styles);
            $headElement->append($fragment);
        }

        $toRemove = [];

        foreach ($this->dom->getElementsByTagName('*') as $element) {
            if ($element->getAttribute('class') == 'viewbrowser') {
                $toRemove[] = $element;
            }
        }

        foreach ($toRemove as $element) {
            $element->remove();
        }
    }
}
