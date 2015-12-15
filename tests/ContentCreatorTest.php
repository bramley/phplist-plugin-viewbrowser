<?php

class ContentCreatorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->users = [
            '2f93856905d26f592c7cfefbff599a0e' => ['id' => 51, 'email' => 'aaa@bbb.com', 'uniqid' => '2f93856905d26f592c7cfefbff599a0e'],
            '' => ['id' => '', 'email' => 'no email', 'uniqid' => ''],
        ];

        $this->usersattributes = [
            'aaa@bbb.com' => ['name' => 'John Smith'],
            '' => ['name' => 'no name',],
        ];

        $this->attachments = [
            28 => [
                [
                    'id' => 12,
                    'description' => 'an attachment',
                    'remotefile' => 'attachment.doc',
                    'size' => 123456,
                ],
                [
                    'id' => 13,
                    'description' => 'another attachment',
                    'remotefile' => 'attachment2.doc',
                    'size' => 7654,
                ]
            ],
        ];

        $this->templates = [
            0 => ['template' => ''],
            1 => ['template' => '<html><head></head><body>template body[CONTENT]</body></html>']
        ];

        $this->templateImages = [
            [
                'id' => 99,
                'template' => 0,
                'templatefilename' => '0ORGANISATIONLOGO500.png',
                'filename' => 'ORGANISATIONLOGO500.png',
                'data' => 'xxxx'
            ]
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
            27 => [
                'message' => 
'<div>
<p>here is a link <a href="http://www.bbc.co.uk">to the bbc</a></p>
<p>a link that contains http <a href="http://www.aaa.com">http://www.aaa.com</a></p>
<p>a link to phplist.com <a href="http://www.phplist.com">phplist</a></p>
<p>here is a cat</p>
</div>'
                ,
                'id' => 27,
                'template' => 1,
                'subject' => 'a test message',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
            28 => [
                'message' => 'here is the message content email address is [email] name is [name%%default name] uniqid is [uniqid] more',
                'id' => 28,
                'template' => 0,
                'subject' => 'a test message',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
            29 => [
                'message' => '<div>not displaying properly? Then [VIEWBROWSER].</div>',
                'id' => 29,
                'template' => 0,
                'subject' => 'a test message',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
            30 => [
                'message' => '<div class="viewbrowser">not displaying properly? Then [VIEWBROWSER].</div>More',
                'id' => 30,
                'template' => 0,
                'subject' => 'a test message',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
            31 => [
                'message' => '<p>Here is the logo <img src="[LOGO]" /></p>here is the message content.',
                'id' => 31,
                'template' => 0,
                'subject' => 'a message with logo but no template',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
            32 => [
                'message' => '<p>Here is the logo <img src="[LOGO]" /></p>here is the message content.',
                'id' => 32,
                'template' => 1,
                'subject' => 'a message with logo and a template',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
            ],
            999 => false,
        ];

        $this->forwardIds = [
            'http://www.bbc.co.uk' => 101,
            './dl.php?id=12' => 102,
            './dl.php?id=13' => 103,
            'http://mysite.com/lists/?m=29&uid=2f93856905d26f592c7cfefbff599a0e&p=view&pi=ViewBrowserPlugin' => 104,
            'http://mysite.com/lists/?m=30&uid=2f93856905d26f592c7cfefbff599a0e&p=view&pi=ViewBrowserPlugin' => 105,
        ];

        $this->daoStub = $this->getMockBuilder('phpList\plugin\ViewBrowserPlugin\DAO')
            ->disableOriginalConstructor()
            ->getMock();

        $this->daoStub->method('message')
            ->will(
                $this->returnCallback(
                    function ($messageId) {
                        return isset($this->templates[$this->messages[$messageId]['template']])
                            ? $this->templates[$this->messages[$messageId]['template']]
                            : false;
                    }
                )
            );
        $this->daoStub->method('userByUniqid')
            ->will(
                $this->returnCallback(
                    function($uniqid) {
                        return $this->users[$uniqid];
                    }
                )
            );
        $this->daoStub->method('forwardId')
            ->will(
                $this->returnCallback(
                    function ($url) {
                        return $this->forwardIds[$url];
                    }
                )
            );
        $this->daoStub->method('getUserAttributeValues')
            ->will(
                $this->returnCallback(
                    function ($email) {
                        return $this->usersattributes[$email];
                    }
                )
            );
        $this->daoStub->method('loadMessageData')
            ->will(
                $this->returnCallback(
                    function ($messageId) {
                        return $this->messages[$messageId];
                    }
                )
            );
        $this->daoStub->method('fetchUrl')
             ->willReturn('here is the remote content');
        $this->daoStub->method('attachments')
            ->will(
                $this->returnCallback(
                    function ($messageId) {
                        return isset($this->attachments[$messageId])
                            ? new ArrayIterator($this->attachments[$messageId])
                            : [];
                    }
                )
            );

        $this->daoStub->method('templateImage')
            ->will(
                $this->returnCallback(
                    function ($templateId, $filename) {
                        $i = array_search("$templateId$filename", array_column($this->templateImages, 'templatefilename'));

                        if ($i === false) {
                            $i = array_search("0$filename", array_column($this->templateImages, 'templatefilename'));
                        }

                        return ($i !== false) ? $this->templateImages[$i] : false;
                    }
                )
            );

        $this->daoAttrStub = $this->getMockBuilder('phpList\plugin\Common\DAO\Attribute')
            ->disableOriginalConstructor()
            ->getMock();

        $this->daoAttrStub->method('attributes')
            ->willReturn([
                ['name' => 'name'],
                ['name' => 'city']
            ]);
    }

    public function createsEmailContentDataProvider()
    {
        return [
            'title element contains message subject' => [
                25,
                '2f93856905d26f592c7cfefbff599a0e',
                ['<title>a test message</title>']
            ],
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
            'adds styles when template not used' => [
                25,
                '2f93856905d26f592c7cfefbff599a0e',
                ['<style></style>']
            ],
            'adds powered by text' => [
                25,
                '2f93856905d26f592c7cfefbff599a0e',
                ['Powered by phplist']
            ],
            'shows no personal fields for anonymous user' => [
                25,
                '',
                ['email address is  name is default name uniqid is  more']
            ],
            'converts a link when link tracking is enabled' => [
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                ['http://mysite.com/lists/lt.php?id=fhoFAAgfBwBEBAU%3D'],
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
            'rejects unknown message' => [
                999,
                '2f93856905d26f592c7cfefbff599a0e',
                ['Message with id 999 does not exist'],
            ],
            'inserts content into template' => [
                27,
                '2f93856905d26f592c7cfefbff599a0e',
                ['<a href="http://www.phplist.com">', 'template body'],
            ],
            'replaces viewbrowser placeholder' => [
                29,
                '2f93856905d26f592c7cfefbff599a0e',
                ['View in your browser'],
            ],
            'removes viewbrowserhide element' => [
                30,
                '2f93856905d26f592c7cfefbff599a0e',
                ['More'],
                ['View in your browser'],
            ],
            'replaces LOGO placeholder when there is no template' => [
                31,
                '2f93856905d26f592c7cfefbff599a0e',
                ['pi=ViewBrowserPlugin&amp;p=image&amp;id=99'],
                ['LOGO'],
            ],
            'replaces LOGO placeholder when there is a template' => [
                32,
                '2f93856905d26f592c7cfefbff599a0e',
                ['pi=ViewBrowserPlugin&amp;p=image&amp;id=99'],
                ['LOGO'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createsEmailContentDataProvider
     */
    public function createsEmailContent($messageId, $uniqid, $expected, $unexpected = array())
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub);
        $result = $cc->createContent($messageId, $uniqid);

        foreach ($expected as $e) {
            $this->assertContains($e, $result);
        }

        foreach ($unexpected as $e) {
            $this->assertNotContains($e, $result);
        }
    }
    /**
     * @test
     */
    public function addsAttachment()
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub);
        $result = $cc->createContent(28, '2f93856905d26f592c7cfefbff599a0e');
        $expected =
'<p>Attachments:<br><img src="./?p=image&amp;pi=CommonPlugin&amp;image=attach.png" alt="" title="">
an attachment 
<a href="http://mysite.com/lists/lt.php?id=fhoFAAsfBw5EBAU%3D">attachment.doc</a>
123.5kB<br><img src="./?p=image&amp;pi=CommonPlugin&amp;image=attach.png" alt="" title="">
another attachment 
<a href="http://mysite.com/lists/lt.php?id=fhoFAAofBw5EBAU%3D">attachment2.doc</a>
7.7kB<br></p>';
        $this->assertContains($expected, $result);
    }
}
