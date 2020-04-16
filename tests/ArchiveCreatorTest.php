<?php

use PHPUnit\Framework\TestCase;

class ArchiveCreatorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->usermessage = [
            '2f93856905d26f592c7cfefbff599a0e' => [
                ['subject' => 'first subject',
                'messageid' => 21,
                'entered' => '2017-10-01',
                ],
                ['subject' => 'second subject',
                'messageid' => 22,
                'entered' => '2017-10-02',
                ],
            ],
        ];

        $this->listmessage = [
            1 => [
                ['subject' => 'first subject',
                'messageid' => 23,
                'entered' => '2017-10-01',
                ],
                ['subject' => 'second subject',
                'messageid' => 24,
                'entered' => '2017-10-02',
                ],
            ],
            2 => [
                ['subject' => 'first subject',
                'messageid' => 25,
                'entered' => '2017-10-01',
                ],
                ['subject' => 'second subject',
                'messageid' => 26,
                'entered' => '2017-10-02',
                ],
            ],
        ];

        $this->lists = [
            1 => ['name' => 'list 1', 'active' => 1],
            2 => ['name' => 'list 2', 'active' => 0],
        ];

        $this->daoStub = $this->getMockBuilder('phpList\plugin\ViewBrowserPlugin\DAO')
            ->disableOriginalConstructor()
            ->getMock();

        $this->daoStub->method('messagesForUser')
            ->will(
                $this->returnCallback(
                    function ($uniqid) {
                        return $this->usermessage[$uniqid];
                    }
                )
            );
        $this->daoStub->method('totalMessagesForUser')
            ->will(
                $this->returnCallback(
                    function ($uniqid) {
                        return count($this->usermessage[$uniqid]);
                    }
                )
            );
        $this->daoStub->method('messagesForList')
            ->will(
                $this->returnCallback(
                    function ($listId) {
                        return $this->listmessage[$listId];
                    }
                )
            );
        $this->daoStub->method('totalMessagesForList')
            ->will(
                $this->returnCallback(
                    function ($listId) {
                        return count($this->listmessage[$listId]);
                    }
                )
            );
        $this->daoStub->method('listById')
            ->will(
                $this->returnCallback(
                    function ($listId) {
                        return $this->lists[$listId];
                    }
                )
            );
    }

    public function createsArchiveDataProvider()
    {
        $data = [
            'contains all messages' => [
                '2f93856905d26f592c7cfefbff599a0e',
                ['first subject', 'second subject']
            ],
            'contains date' => [
                '2f93856905d26f592c7cfefbff599a0e',
                ['2017-10-02']
            ],
            'contains view in browser url' => [
                '2f93856905d26f592c7cfefbff599a0e',
                [
                'pi=ViewBrowserPlugin&amp;p=view&amp;m=21&amp;uid=2f93856905d26f592c7cfefbff599a0e',
                'pi=ViewBrowserPlugin&amp;p=view&amp;m=22&amp;uid=2f93856905d26f592c7cfefbff599a0e'
                ]
            ],
        ];

        return $data;
    }

    /**
     * @test
     * @dataProvider createsArchiveDataProvider
     */
    public function createsArchive($uniqid, $expected, $unexpected = array())
    {
        $archive = new phpList\plugin\ViewBrowserPlugin\ArchiveCreator($this->daoStub);
        $result = $archive->createSubscriberArchive($uniqid);

        foreach ($expected as $e) {
            $this->assertStringContainsString($e, $result);
        }

        foreach ($unexpected as $e) {
            $this->assertNotContains($e, $result);
        }
    }

    public function allowAccessDataProvider()
    {
        $data = [
            'createsArchivePublicList' => [
                '',
                1,
                'first subject',
            ],
            'notAllowArchivePrivateList' => [
                '',
                2,
                'Not allowed to view campaigns for list 2',
            ],
            'createsArchivePrivateList' => [
                '2 3',
                2,
                '26',
            ],
        ];

        return $data;
    }

    /**
     * @test
     * @dataProvider allowAccessDataProvider
     */
    public function allowAccessToArchive($allowed, $listId, $expected)
    {
        global $phplist_config;

        $phplist_config['viewbrowser_anonymous'] = true;
        $phplist_config['viewbrowser_allowed_lists'] = $allowed;

        $archive = new phpList\plugin\ViewBrowserPlugin\ArchiveCreator($this->daoStub);
        $result = $archive->createListArchive($listId);

        $this->assertStringContainsString($expected, $result);
    }
}
