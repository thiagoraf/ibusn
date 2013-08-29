<?php

namespace SocialNetwork\API\Provider;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase,
    Symfony\Component\Yaml\Parser,
    SocialNetwork\API\Provider\DbGroupProvider,
    SocialNetwork\API\Provider\Criteria;

class DbGroupProviderTest extends WebTestCase
{
    private $rProvider;
    private $group;
    private $criteria;

    protected function setUp()
    {
        $yaml = new Parser();
        $map = $yaml->parse(file_get_contents(__DIR__ . '/../../../../../app/config/parameters.yml'));

        $client = static::createClient();
        $this->rProvider = new DbGroupProvider( $client->getContainer(), $map['parameters']['provider_group_map'] );

        $this->group = array(
            'name' => 'GROUPTESTECASE',
            'uid' => 'GROUPTESTECASE_UID',
            'description' => 'GROUPTESTECASE',
            'members' => array( 'GROUPTESTECASE' )
        );

        $this->criteria = new Criteria();
    }

    protected function tearDown()
    {
        $this->rProvider = null;
    }

    function getGroup($name){

        $this->criteria->add('name', $name);
        $group = $this->rProvider->find( $this->criteria );
        return $group[0];
    }

    public function testCreate()
    {
        $group = $this->rProvider->create(
            $this->group['uid'],
            $this->group['name'],
            $this->group['description'],
            $this->group['members']
        );

        $this->assertInternalType('int', $group);
    }

    public function testGet()
    {
        $group = $this->getGroup('GROUPTESTECASE');

        $group = $this->rProvider->get( $group['id'] );
        $this->assertEquals($group['name'], $this->group['name']);
        $this->assertEquals($group['description'], $this->group['description']);
        $this->assertEquals($group['uid'], $this->group['uid']);
        $this->assertCount(1, $this->group['members']);
    }

    public function testGetUid()
    {
        $group = $this->getGroup('GROUPTESTECASE');

        $group = $this->rProvider->getByUid( $group['uid'] );
        $this->assertEquals($group['name'], $this->group['name']);
        $this->assertEquals($group['description'], $this->group['description']);
        $this->assertEquals($group['uid'], $this->group['uid']);
        $this->assertCount(1, $this->group['members']);
    }

    public function testFind()
    {
        $this->criteria->add('name','GROUPTESTECASE');
        $group = $this->rProvider->find( $this->criteria );

        $this->assertCount(1, $group);
        $group = $group[0];

        $this->assertEquals($group['name'], $this->group['name']);
        $this->assertEquals($group['description'], $this->group['description']);
        $this->assertCount(1, $this->group['members']);
    }

    public function testUpdate()
    {
        $group = $this->getGroup('GROUPTESTECASE');

        $this->group['name'] = $this->group['name'].'ALTER';
        $this->group['uid'] = $this->group['uid'].'ALTER';
        $this->group['description'] = $this->group['description'].'ALTER';
        $this->group['members'] = array( 'GROUPTESTECASE', 'GROUPTESTECASE2' );

        $alter = $this->rProvider->update(
            $group['id'],
            $this->group['uid'],
            $this->group['name'],
            $this->group['description'],
            $this->group['members']
        );

        $this->assertTrue( $alter );
    }

    public function testSetUserGroup()
    {
        $group = $this->getGroup('GROUPTESTECASEALTER');
        $add  = $this->rProvider->setUserGroup('USERTESTECASEADDTOGROUP', $group['uid']);

        $this->assertTrue( $add );
    }

    public function testGetUserGroups()
    {
        $groups  = $this->rProvider->getUserGroups('USERTESTECASEADDTOGROUP');
        $this->assertCount(1, $groups);
    }

    public function testDeleteUserGroup()
    {
        $group = $this->getGroup('GROUPTESTECASEALTER');
        $remove  = $this->rProvider->deleteUserGroup('USERTESTECASEADDTOGROUP', $group['uid']);

        $this->assertTrue( $remove );
    }

    public function testDelete()
    {
        $group = $this->getGroup('GROUPTESTECASEALTER');
        $group = $this->rProvider->delete( $group['id'] );

        $this->assertInternalType('bool', $group);
    }

}