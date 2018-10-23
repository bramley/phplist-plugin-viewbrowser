<?php

class ContentCreatorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->users = [
            '2f93856905d26f592c7cfefbff599a0e' => [
                'id' => 51,
                'email' => 'aaa@bbb.com',
                'uniqid' => '2f93856905d26f592c7cfefbff599a0e',
                'uuid' => 'e446db8d-7bb0-4811-a054-d2951bf4176d'
            ],
            '' => ['id' => '', 'email' => 'no email', 'uniqid' => '', 'uuid' => ''],
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
                'uuid' => '5a4d86ce-986d-4c00-a44f-3c8fa717759e',
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
                'uuid' => '2f404bcc-e060-4675-8d50-96b329962f92',
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
                'uuid' => '1f813f71-56fc-4d9a-a1c4-74845523547b',
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
                'uuid' => '12a78839-e6af-4f44-8a6b-6a02a51796b2',
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
                'uuid' => '81b6adf0-1b00-4b55-8db1-ad0c1d41430c',
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
                'uuid' => 'fe1cf2e3-18a8-42bf-a840-957274bb6421',
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
                'uuid' => '4fb7dc30-959b-427d-beaf-1040d09fda8a',
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
                'uuid' => '1bfde45d-3b12-4a7b-849a-35a47276d902',
            ],
            33 => [
                'message' => 'here is the message content email address is [email] name is [name%%default name] uniqid is [uniqid] more',
                'id' => 33,
                'template' => 0,
                'subject' => 'a test message',
                'footer' =>
'--
If you do not want to receive any more newsletters,  [UNSUBSCRIBE]
To update your preferences and to unsubscribe visit [PREFERENCES]
Forward a Message to Someone [FORWARD]',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
                'uuid' => 'bee49a0b-853c-4332-a252-553b3a6ef5ae',
            ],
            34 => [
                'message' => 'here is the message content email address is [email] name is [name%%default name] uniqid is [uniqid] more',
                'id' => 34,
                'template' => 0,
                'subject' => 'a test message',
                'footer' =>
'--
  
    <div class="footer" style="text-align:left; font-size: 75%;">
      <p>This message was sent to [EMAIL] by [FROMEMAIL]</p>
      <p>To forward this message, please do not use the forward button of your email application, because this message was made specifically for you only. Instead use the <a href="[FORWARDURL]">forward page</a> in our newsletter system.<br/>
      To change your details and to choose which lists to be subscribed to, visit your personal <a href="[PREFERENCESURL]">preferences page</a><br/>
      Or you can <a href="[UNSUBSCRIBEURL]">opt-out completely</a> from all future mailings.</p>
    </div>

  ',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
                'uuid' => 'ad51c93a-f6e6-4a68-aae0-d962e7c0fcb2',
            ],
            35 => [
                'message' => 'here is the message content email address is [email] name is [name%%default name] uniqid is [uniqid] more',
                'id' => 35,
                'template' => 0,
                'subject' => 'a test message',
                'footer' => '',
                'fromemail' => 'from@email.com',
                'sendmethod' => 'xxx',
                'sendurl' => '',
                'uuid' => '12a78839-e6af-4f44-8a6b-6a02a51796b2',
            ],
            999 => false,
        ];

        $this->userMessage = [
            '2f93856905d26f592c7cfefbff599a0e' => [
                25, 26, 27, 28, 29
            ],
        ];

        $this->listForMessage = [
            25 => [
                ['id' => 1, 'active' => 1],
            ],
            26 => [
                ['id' => 2, 'active' => 1],
            ],
            28 => [
                ['id' => 1, 'active' => 0],
            ],
            35 => [
                ['id' => 3, 'active' => 0],
            ],
        ];

        $this->forwardIds = [
            'http://www.bbc.co.uk' => 101,
            './dl.php?id=12' => 102,
            './dl.php?id=13' => 103,
            'http://mysite.com/lists/?m=29&uid=2f93856905d26f592c7cfefbff599a0e&p=view&pi=ViewBrowserPlugin' => 104,
            'http://mysite.com/lists/?m=30&uid=2f93856905d26f592c7cfefbff599a0e&p=view&pi=ViewBrowserPlugin' => 105,
            'http://mysite.com/lists/?p=unsubscribe&uid=2f93856905d26f592c7cfefbff599a0e' => 106,
            'http://mysite.com/lists/?p=preferences&uid=2f93856905d26f592c7cfefbff599a0e' => 107,
            'http://mysite.com/lists/?p=forward&uid=2f93856905d26f592c7cfefbff599a0e&mid=33' => 108,
            'http://mysite.com/lists/?p=forward&uid=2f93856905d26f592c7cfefbff599a0e&mid=34' => 109,
        ];

        $this->forwardUuids = [
            'http://www.bbc.co.uk' => 'a207d056-4982-4ecc-becf-b92220d6dd9f',
            './dl.php?id=12' => '1444708a-62f0-44af-8ca6-7adbe9cd02bf',
            './dl.php?id=13' => 'b5624bfd-f972-4cbe-8c52-d74cb12ea953',
            'http://mysite.com/lists/?m=29&uid=2f93856905d26f592c7cfefbff599a0e&p=view&pi=ViewBrowserPlugin' => '2df41324-aa19-4619-aa8a-1db5a5c02c7f',
            'http://mysite.com/lists/?m=30&uid=2f93856905d26f592c7cfefbff599a0e&p=view&pi=ViewBrowserPlugin' => '010287e5-c201-4d74-93d8-0e5ec6eaa68d',
            'http://mysite.com/lists/?p=unsubscribe&uid=2f93856905d26f592c7cfefbff599a0e' => '717c417c-934d-40c1-8739-33fe34c62fb9',
            'http://mysite.com/lists/?p=preferences&uid=2f93856905d26f592c7cfefbff599a0e' => '9d7c0c8c-1dc1-4597-899b-215c3951e756',
            'http://mysite.com/lists/?p=forward&uid=2f93856905d26f592c7cfefbff599a0e&mid=33' => 'b10084da-1d0c-4286-8838-52fb0f9ff620',
            'http://mysite.com/lists/?p=forward&uid=2f93856905d26f592c7cfefbff599a0e&mid=34' => '1636661d-059f-4009-a8b6-e1c36223ac07',
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
        $this->daoStub->method('forwardUuid')
            ->will(
                $this->returnCallback(
                    function ($url) {
                        return $this->forwardUuids[$url];
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

        $this->daoStub->method('wasUserSentMessage')
            ->will(
                $this->returnCallback(
                    function ($messageId, $uid) {
                        return in_array($messageId, $this->userMessage[$uid]);
                    }
                )
            );

        $this->daoStub->method('listsForMessage')
            ->will(
                $this->returnCallback(
                    function ($messageId) {
                        $a = $this->listForMessage[$messageId] ?? $this->listForMessage[25];
                        return new ArrayIterator($a);
                        //~ return new ArrayIterator($this->listForMessage[$messageId]);
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
        $data = [
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
            'converts text footer to html' => [
                33,
                '2f93856905d26f592c7cfefbff599a0e',
                ["<br>\nIf you do not want to receive any more newsletters,"],
            ],
        ];

        return $data;
    }

    /**
     * @test
     * @dataProvider createsEmailContentDataProvider
     */
    public function createsEmailContent($messageId, $uniqid, $expected, $unexpected = array())
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub, true, '3.2.0');
        $result = $cc->createContent($messageId, $uniqid);

        foreach ($expected as $e) {
            $this->assertContains($e, $result);
        }

        foreach ($unexpected as $e) {
            $this->assertNotContains($e, $result);
        }
    }

    public function encodesLinksCurrentDataProvider()
    {
        $data = [
            'converts a link when link tracking is enabled' => [
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                ['http://mysite.com/lists/lt.php?tid=fhpVAglUUQYNBxkGDl1XTFUEAgIZW1BUXxpUDQBXBlI'],
                ['<a href="http://www.bbc.co.uk">'],
            ],
        ];

        return $data;
    }
    /**
     * @test
     * @dataProvider encodesLinksCurrentDataProvider
     */
    public function encodesLinksCurrent($messageId, $uniqid, $expected, $unexpected = array())
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub, true, '3.3.0');
        $result = $cc->createContent($messageId, $uniqid);

        foreach ($expected as $e) {
            $this->assertContains($e, $result);
        }

        foreach ($unexpected as $e) {
            $this->assertNotContains($e, $result);
        }
    }

    public function encodesLinksPreviousDataProvider()
    {
        $data = [
            'converts a link when link tracking is enabled' => [
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                ['http://mysite.com/lists/lt.php?id=fhoFAAgfBwBEBAU'],
                ['<a href="http://www.bbc.co.uk">'],
            ],
        ];

        return $data;
    }
    /**
     * @test
     * @dataProvider encodesLinksPreviousDataProvider
     */
    public function encodesLinksPrevious($messageId, $uniqid, $expected, $unexpected = array())
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub, true, '3.2.0');
        $result = $cc->createContent($messageId, $uniqid);

        foreach ($expected as $e) {
            $this->assertContains($e, $result);
        }

        foreach ($unexpected as $e) {
            $this->assertNotContains($e, $result);
        }
    }

    public function doesNotEncodeLinksDataProvider()
    {
        $data = [
            'does not convert a link when link tracking is disabled' => [
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                ['<a href="http://www.bbc.co.uk">'],
            ],
        ];

        return $data;
    }
    /**
     * @test
     * @dataProvider doesNotEncodeLinksDataProvider
     */
    public function doesNotEncodeLinks($messageId, $uniqid, $expected, $unexpected = array())
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub, false, '3.2.0');
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
    public function createsHtmlFormatFooter()
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub, false, getConfig('version'));
        $result = $cc->createContent(34, '2f93856905d26f592c7cfefbff599a0e');
        $expected = '<div class="footer" style="text-align:left; font-size: 75%;">';
        $this->assertContains($expected, $result);
        $expected2 = '<a href="http://mysite.com/lists/?p=preferences&amp;uid=2f93856905d26f592c7cfefbff599a0e">preferences page</a>';
        $this->assertContains($expected2, $result);
    }
    /**
     * @test
     */
    public function addsAttachment()
    {
        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub, false, getConfig('version'));
        $result = $cc->createContent(28, '2f93856905d26f592c7cfefbff599a0e');
        $expected =
'<p>Attachments:<br><img src="./?p=image&amp;pi=CommonPlugin&amp;image=attach.png" alt="" title="">
an attachment
<a href="./dl.php?id=12">attachment.doc</a>
123.5kB<br><img src="./?p=image&amp;pi=CommonPlugin&amp;image=attach.png" alt="" title="">
another attachment
<a href="./dl.php?id=13">attachment2.doc</a>
7.7kB<br></p>';
        $this->assertContains($expected, $result);
    }

    public function allowAccessDataProvider()
    {
        $data = [
            'allowAnonymousToPublicList' => [
                true,
                '',
                25,
                '',
                'here is the message content',
            ],
            'allowAnonymousToSpecificList' => [
                true,
                '2',
                26,
                '',
                'here is a cat',
            ],
            'notAllowAnonymousToPrivateList' => [
                true,
                '',
                28,
                '',
                'Not allowed to view message 28',
            ],
            'notAllowAnonymousToSpecificList' => [
                true,
                '3 4',
                26,
                '',
                'Not allowed to view message 26',
            ],
            'allowUserSentMessage' => [
                false,
                '',
                26,
                '2f93856905d26f592c7cfefbff599a0e',
                'here is a cat',
            ],
            'allowUserMessagePublicList' => [
                false,
                '',
                30,
                '2f93856905d26f592c7cfefbff599a0e',
                'More',
            ],
            'notAllowUserMessagePrivateList' => [
                false,
                '',
                35,
                '2f93856905d26f592c7cfefbff599a0e',
                'Not allowed to view message',
            ],
            'allowUserMessageAllowedList' => [
                true,
                '3 4',
                35,
                '2f93856905d26f592c7cfefbff599a0e',
                'here is the message content',
            ],
            'notAllowUserMessageNotAllowedList' => [
                true,
                '4 5',
                35,
                '2f93856905d26f592c7cfefbff599a0e',
                'Not allowed to view message',
            ],
        ];

        return $data;
    }

    /**
     * @test
     * @dataProvider allowAccessDataProvider
     */
    public function allowAnonymousAndUserAccess($anonymous, $allowed, $mid, $uid, $expected)
    {
        global $phplist_config;

        $phplist_config['viewbrowser_anonymous'] = $anonymous;
        $phplist_config['viewbrowser_allowed_lists'] = $allowed;

        $cc = new phpList\plugin\ViewBrowserPlugin\ContentCreator($this->daoStub, $this->daoAttrStub, false, getConfig('version'));
        $result = $cc->createContent($mid, $uid);
        $this->assertContains($expected, $result);
    }
}
