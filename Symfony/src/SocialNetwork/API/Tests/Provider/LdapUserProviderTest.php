<?php

namespace SocialNetwork\API\Tests\Provider;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase,
    Symfony\Component\Yaml\Parser,
    SocialNetwork\API\Provider\LdapUserProvider,
    SocialNetwork\API\Provider\Criteria;

class LdapUserProviderTest extends WebTestCase
{
    private $uProvider;
    private $user;
    private $criteria;

    protected function setUp()
    {
        $yaml = new Parser();
        $map = $yaml->parse(file_get_contents(__DIR__ . '/../../../../../app/config/parameters.yml'));

        $client = static::createClient();
        $this->uProvider = new LdapUserProvider( $client->getContainer(), $map['parameters']['provider_user_map'] );

        $this->user = array(
            'name' => 'USERTESTECASE',
            'uid' => 'USERTESTECASE',
            'password' => 'USERTESTECASE',
            'attributes' => array('sn'=>'USERTESTECASE' )
        );

        $this->criteria = new Criteria();
    }

    protected function tearDown()
    {
        $this->uProvider = null;
    }

    function getUser($uid){

        $this->criteria->add('uid', $uid);
        $user = $this->uProvider->find( $this->criteria );
        return $user[0];

    }

    public function testCreate()
    {
        $this->user['id'] = $this->uProvider->create(
            $this->user['name'],
            $this->user['uid'],
            $this->user['password'],
            $this->user['attributes']

        );

        $this->assertInternalType('int', $this->user['id']);
    }

    public function testFind()
    {
        $this->criteria->add('uid','USERTESTECASE');
        $user = $this->uProvider->find( $this->criteria );

        $user = $user[0];

        $this->assertEquals($user['name'], $this->user['name']);
        $this->assertEquals($user['uid'], $this->user['uid']);
    }

    public function testGet()
    {
        $user = $this->getUser('USERTESTECASE');
        $user = $this->uProvider->get( $user['id'] );
        $this->assertEquals($user['name'], $this->user['name']);
        $this->assertEquals($user['uid'], $this->user['uid']);
    }

    public function testGetByUid()
    {
        $user = $this->uProvider->getByUid('USERTESTECASE');
        $this->assertEquals($user['name'], $this->user['name']);
        $this->assertEquals($user['uid'], $this->user['uid']);
    }

    public function testUpdate()
    {
        $user = $this->getUser('USERTESTECASE');

        $this->user['name'] = $this->user['name'].'ALTER';
        $this->user['uid'] = $this->user['uid'].'ALTER';
        $this->user['password'] = $this->user['password'].'ALTER';

        $alter = $this->uProvider->update(
            $user['id'],
            $this->user['name'],
            $this->user['uid'],
            $this->user['password'],
            $this->user['attributes']
        );

        $user = $this->uProvider->get( $user['id'] );

        $this->assertTrue( $alter );
        $this->assertEquals($user['uid'], $this->user['uid']);
        $this->assertEquals($user['name'], $this->user['name']);
    }

    public function testDelete()
    {
        $user = $this->getUser('USERTESTECASEALTER');
        $user = $this->uProvider->delete( $user['id'] );
        $this->assertInternalType('bool', $user);
    }
}