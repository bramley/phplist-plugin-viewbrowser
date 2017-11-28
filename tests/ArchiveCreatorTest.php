<?php

class ArchiveCreatorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
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
                ['02/10/17']
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
        $result = $archive->createArchive($uniqid);

        foreach ($expected as $e) {
            $this->assertContains($e, $result);
        }

        foreach ($unexpected as $e) {
            $this->assertNotContains($e, $result);
        }
    }
}
