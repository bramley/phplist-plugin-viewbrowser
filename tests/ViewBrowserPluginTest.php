<?php

class ViewBrowserPluginTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->v = new ViewBrowserPlugin;
        $this->v->activate();

        $this->users = [
            '2f93856905d26f592c7cfefbff599a0e' => ['id' => 51, 'email' => 'aaa@bbb.com', 'uniqid' => '123456'],
            '' => ['id' => '', 'email' => 'no email', 'uniqid' => ''],
        ];

        $this->usersattributes = [
            'aaa@bbb.com' => ['name' => 'John Smith'],
            '' => ['name' => 'no name',],
        ];

        $this->messages = [
            25 => [
                'message' => 'here is the message content email address is [email] name is [name%%default name] uniqid is [uniqid] more',
                'id' => 25,
                'template' => 0,
                'subject' => 'a test message',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
            26 => [
                'message' => 
'<div>
<p>here is a link <a href="http://www.bbc.co.uk">to the bbc</a></p>
<p>a link that contains http <a href="http://www.aaa.com">http://www.aaa.com</a></p>
<p>a link to phplist.com <a href="http://www.phplist.com">phplist</a></p>
<p>here is a cat</p>
</div>'

                ,
                'id' => 26,
                'template' => 0,
                'subject' => 'a test message',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
        ];
        $this->daoStub = $this->getMockBuilder('phpList\plugin\ViewBrowserPlugin\DAO')
            ->disableOriginalConstructor()
            ->getMock();

        $this->daoStub->method('message')
             ->willReturn(['template' => 0]);
        $this->daoStub->method('userByUniqid')
            ->will($this->returnCallback(function($uniqid) {
                return $this->users[$uniqid];
            }));
        $this->daoStub->method('forwardId')
            ->willReturn(1234);
        $this->daoStub->method('getUserAttributeValues')
            ->will($this->returnCallback(function ($email) {
                return $this->usersattributes[$email];
            }));
        $this->daoStub->method('loadMessageData')
            ->will($this->returnCallback(function ($messageId) {
                return $this->messages[$messageId];
            }));
        $this->daoStub->method('fetchUrl')
             ->willReturn('here is the remote content');

        $this->daoAttrStub = $this->getMockBuilder('phpList\plugin\Common\DAO\Attribute')
            ->disableOriginalConstructor()
            ->getMock();

        $this->daoAttrStub->method('attributes')
            ->willReturn([
                ['name' => 'name'],
                ['name' => 'city']
            ]);
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
            $this->v->parseOutgoingHTMLMessage($mid, $content, $email, $user)
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
            $this->v->parseOutgoingTextMessage($mid, $content, $email, $user)
        );
    }

    public function createsEmailContentDataProvider()
    {
        return [
            'replaces email placeholder' => [
                25,
                '2f93856905d26f592c7cfefbff599a0e',
                ['email address is aaa@bbb.com']
            ],
            'replaces user attribute' => [
                25,
                '2f93856905d26f592c7cfefbff599a0e',
                ['name is John Smith']
            ],
            'shows no personal fields for anonymous user' => [
                25,
                '',
                ['email address is  name is default name uniqid is  more']
            ],
            'converts a link when link tracking is enabled' => [
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                ['http://mysite.com/lists/lt.php?id=MzczNTkyODU1OQ%3D%3D'],
                ['<a href="http://www.bbc.co.uk">'],
            ],
            'does not convert a link whose text contains http' => [
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                ['<a href="http://www.aaa.com">'],
            ],
            'does not convert a link to phplist.com' => [
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                ['<a href="http://www.phplist.com">'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createsEmailContentDataProvider
     */
    public function createsEmailContent($messageId, $uniqid, $expected, $unexpected = array())
    {
        $message = $this->messages[$messageId];

        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub);
        $result = $cc->createContent($messageId, $uniqid);
        $this->assertContains("<title>{$message['subject']}</title>", $result);

        foreach ($expected as $e) {
            $this->assertContains($e, $result);
        }

        foreach ($unexpected as $e) {
            $this->assertNotContains($e, $result);
        }
    }
}
