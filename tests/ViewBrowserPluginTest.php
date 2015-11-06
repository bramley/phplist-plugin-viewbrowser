<?php

class ViewBrowserPluginTest extends PHPUnit_Framework_TestCase
{
    private $pi;

    protected function setUp()
    {
        $this->pi = new ViewBrowserPlugin;
        $this->pi->activate();
    }

    public function parseHtmlDataProvider()
    {
        return [
            'lower case placeholder' => [
                12, 'here is the first content [viewbrowser]', 'fsdf@sfsd.com',  array('uniqid' => '0987654321'),
                'here is the first content <a href="http://mysite.com/lists/?m=12&amp;uid=0987654321&amp;p=view&amp;pi=ViewBrowserPlugin" >View in your browser</a>'
            ],
            'upper case placeholder' => [
                13, 'here is the second content [VIEWBROWSER]', 'fsdf@sfsd.com',  array('uniqid' => '1234567890'),
                'here is the second content <a href="http://mysite.com/lists/?m=13&amp;uid=1234567890&amp;p=view&amp;pi=ViewBrowserPlugin" >View in your browser</a>'
            ],
            'url placeholder' => [
                13, 'here is the third content [VIEWBROWSERURL]', 'fsdf@sfsd.com',  array('uniqid' => '1234567890'),
                'here is the third content http://mysite.com/lists/?m=13&amp;uid=1234567890&amp;p=view&amp;pi=ViewBrowserPlugin'
            ],
        ];
    }
    /**
     * @test
     * @dataProvider parseHtmlDataProvider
     */
    public function parseOutgoingHTMLMessage($mid, $content, $email, $user, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->pi->parseOutgoingHTMLMessage($mid, $content, $email, $user)
        );
    }

    public function parseTextDataProvider()
    {
        return [
            'lower case placeholder' => [
                12, 'here is the first content [viewbrowser]', 'fsdf@sfsd.com',  array('uniqid' => '0987654321'),
                "here is the first content View in your browser http://mysite.com/lists/?m=12&uid=0987654321&p=view&pi=ViewBrowserPlugin"
            ],
            'upper case placeholder' => [
                13, 'here is the second content [VIEWBROWSER]', 'fsdf@sfsd.com',  array('uniqid' => '1234567890'),
                "here is the second content View in your browser http://mysite.com/lists/?m=13&uid=1234567890&p=view&pi=ViewBrowserPlugin"
            ],
            'url placeholder' => [
                13, 'here is the third content [VIEWBROWSERURL]', 'fsdf@sfsd.com',  array('uniqid' => '1234567890'),
                'here is the third content http://mysite.com/lists/?m=13&uid=1234567890&p=view&pi=ViewBrowserPlugin'
            ],
        ];
    }
    /**
     * @test
     * @dataProvider parseTextDataProvider
     */
    public function parseOutgoingTextMessage($mid, $content, $email, $user, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->pi->parseOutgoingTextMessage($mid, $content, $email, $user)
        );
    }
}
