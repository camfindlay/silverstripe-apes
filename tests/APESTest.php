<?php

class APESTest extends SapphireTest
{


    public static $fixture_file = 'apes/tests/APESTest.yml';

    public function setUp()
    {

        Config::inst()->update(
            'Injector',
            'Mailchimp',
            array('class' => 'MockMailchimp')
        );
        parent::setUp();

    }

    public function tearDown()
    {
        parent::tearDown();

    }

    public function testSubscribe()
    {

        $member = Member::get()->filter('Email', 'joe@bloggs.com')->first();

        $member->subscribeMailChimpUser();
        $member->write();

        $this->assertEquals('67890', $member->MailchimpID);

    }
}