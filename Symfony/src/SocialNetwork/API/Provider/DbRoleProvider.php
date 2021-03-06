<?php

namespace SocialNetwork\API\Provider;

use SocialNetwork\API\Interfaces\IRoleProvider,
    SocialNetwork\API\Entity\Role,
    SocialNetwork\API\Entity\RoleMember,
    SocialNetwork\API\Helper\AttributeMap as MAP;

/**
 * Class DbRoleProvider
 * @package SocialNetwork\API\Provider
 */
class DbRoleProvider implements IRoleProvider
{
    protected $dbService, $roleProvider, $map, $logger;

    /**
     * @param $container Symfony service container
     * @param array $map Map of default attributes
     */
    public function __construct($container, $map)
    {
        $this->map = $map;
        $this->logger = $container->get('logger');
        $this->groupProvider = $container->get('API.GroupProvider');
        $this->dbService = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * Create new Role
     *
     * @param $uid Uid to role
     * @param $name Name to role
     * @param $description Description to role
     * @param array $members Uid of the users to the role
     * @param array $attributes  Custom attributes to group
     * @return bool|int
     */
    public function create( $uid, $name, $description, array $members = array(), array $attributes = array())
    {
        $role = new Role();

        $role->setName( $name );
        $role->setDescription( $description );
        $role->setUid( $uid );

        foreach($attributes as $key => $val)
        {
            $role->set{ ucfirst($key) }( $val );
        }

        $this->dbService->persist( $role );

        foreach($members as $member){

            $roleMember = new RoleMember();
            $roleMember->setMemberUid( $member );
            $roleMember->setRoleUid( $role->getUid() );

            $this->dbService->persist( $roleMember );
        }

        $this->dbService->flush();

        if( !$role->getId() )
        {
            $this->logger->err( 'Error in create role' );
            return false;
        }

        return $role->getId();
    }

    /**
     * Update exist Role
     *
     * @param $id Id of Role
     * @param $uid Uid to role
     * @param $name New name to Role
     * @param $description New description to Role
     * @param array $members UID of the users to the group
     * @param array $attributes Custom attributes
     * @return bool
     */
    public function update($id, $uid, $name, $description, array $members = array(), array $attributes = array())
    {
        $role = $this->dbService->find('SocialNetwork\API\Entity\Role', $id);

        if( $name ) $role->setName( $name );
        if( $description ) $role->setDescription( $description );
        if( $uid ) $role->setUid( $uid );

        foreach($attributes as $key => $val)
        {
            $role->set{ ucfirst($key) }( $val );
        }

        $this->dbService->merge( $role );

        $this->dbService->createQueryBuilder()
            ->delete( 'SocialNetwork\API\Entity\RoleMember', 'rm' )
            ->where('rm.roleUid = ?1')
            ->setParameter(1 , $uid )
            ->getQuery()
            ->getResult();

        foreach($members as $member)
        {
            $roleMember = new RoleMember();
            $roleMember->setMemberUid( $member );
            $roleMember->setRoleUid( $role->getUid() );
            $this->dbService->persist( $roleMember );
        }

        $this->dbService->flush();

        if( !$role->getId() )
        {
            $this->logger->info( sprintf('User not found %u', $id) );
            return false;
        }

        return true;
    }

    /**
     * Read Role
     *
     * @param $id Id of Role
     * @param array $attributes Attributes to return
     * @return array
     * @throws \Exception
     */
    public function get($id, Array $attributes = array())
    {
        $role = $this->dbService->createQueryBuilder()
            ->select( MAP::mapDbAttributes('r',$attributes)  )
            ->from('SocialNetwork\API\Entity\Role', 'r')
            ->where('r.id = ?1')
            ->setParameter(1 , $id )
            ->getQuery()
            ->getArrayResult();

        if( empty( $role ) )
        {
            $this->logger->info( sprintf('Role not found %u', $id ));
            return false;
        }

        return $role[0];
    }

    /**
     * Read Role
     *
     * @param $uid uid of Role
     * @param array $attributes Attributes to return
     * @return array
     * @throws \Exception
     */
    public function getByUid($uid, Array $attributes = array())
    {
        $role = $this->dbService->createQueryBuilder()
            ->select( MAP::mapDbAttributes('r',$attributes)  )
            ->from('SocialNetwork\API\Entity\Role', 'r')
            ->where('r.uid = ?1')
            ->setParameter(1 , $uid )
            ->getQuery()
            ->getArrayResult();

        if( empty( $role ) )
        {
            $this->logger->info( sprintf('Role not found %s', $uid ));
            return false;
        }

        return $role[0];
    }


    /**
     * Find Role
     *
     * @param Criteria $criteria Criteria filter
     * @param array $attributes Attributes to return
     * @return array
     */
    public function find( Criteria $criteria , array $attributes = array() )
    {
        $fp = new FilterProvider( $criteria );
        $fpr = $fp->formatDQL('r');

        $result = $this->dbService->createQueryBuilder()
            ->select( MAP::mapDbAttributes('r' , $attributes)  )
            ->from('SocialNetwork\API\Entity\Role', 'r')
            ->where($fpr['dql'])
            ->setParameters($fpr['parameters'] )
            ->getQuery()
            ->getArrayResult();

        if( empty( $result ) )
        {
            $this->logger->info('Role without result');
            return false;
        }

        return $result;
    }

    /**
     * Delete Role
     *
     * @param $id Id of Role
     * @return bool
     * @throws \Exception
     */
    public function delete( $id )
    {
        $role = $this->dbService->find('SocialNetwork\API\Entity\Role', $id);

        if($role){
            $this->dbService->remove($role);

            $this->dbService->createQueryBuilder()
                ->delete('SocialNetwork\API\Entity\RoleMember', 'rm' )
                ->andWhere('rm.roleUid = ?1')
                ->setParameter(1 , $role->getUid() )
                ->getQuery()
                ->execute();

            $this->dbService->flush();

            if( $role->getId() )
            {
                $this->logger->info( sprintf('Error in remove role %u', $id ));
                return false;
            }

            return true;
        }else
        {
            $this->logger->info( sprintf('Role not found %s', $uid ));
            return false;
        }
    }

    /**
     * Add role to User
     *
     * @param $user User Uid
     * @param $role Role uid
     * @return bool
     * @throws \Exception
     */
    public function setUserRole($user, $role)
    {
        $roleMember = $this->dbService->createQueryBuilder()
            ->select( 'rm' )
            ->from('SocialNetwork\API\Entity\RoleMember', 'rm' )
            ->where('rm.memberUid = ?1')
            ->andWhere('rm.roleUid = ?2')
            ->setParameter(1 , $user )
            ->setParameter(2 , $role )
            ->getQuery()
            ->getArrayResult();

        if( empty( $roleMember ) )
        {
            $roleMember = new RoleMember();
            $roleMember->setMemberUid( $user );
            $roleMember->setRoleUid( $role );

            $this->dbService->persist( $roleMember );
            $this->dbService->flush();

            if( !$roleMember->getId())
            {
                $this->logger->info( sprintf('Error in add user member %s', $user ));
                return false;
            }
        }

        return true;
    }

    /**
     * Add role to Group
     *
     * @param $group Group Uid
     * @param $role Role uid
     * @return bool
     * @throws \Exception
     */
    public function setGroupRole($group, $role)
    {
        $roleMember = $this->dbService->createQueryBuilder()
            ->select( 'rm' )
            ->from('SocialNetwork\API\Entity\RoleMember', 'rm' )
            ->where('rm.memberUid = ?1')
            ->andWhere('rm.roleUid = ?2')
            ->setParameter(1 , $group )
            ->setParameter(2 , $role )
            ->getQuery()
            ->getArrayResult();

        if( empty( $roleMember ) )
        {
            $roleMember = new RoleMember();
            $roleMember->setMemberUid( $group );
            $roleMember->setRoleUid( $role );

            $this->dbService->persist( $roleMember );
            $this->dbService->flush();

            if( !$roleMember->getId())
            {
                $this->logger->info( sprintf('Error in add group member %s', $group ));
                return false;
            }
        }

        return true;
    }

    /**
     * Return all Roles of the user
     *
     * @param $user User uid
     * @param array $attributes Attributes to return
     * @param bool $groupRoles Return the roles of groups inserted in users
     * @return array
     */
    public function getUserRoles($user, array $attributes = array(), $groupRoles = true)
    {
        $members = array( $user );

        if( $groupRoles )
        {
            $groups = $this->groupProvider->getUserGroups( $user, array('id') );

            if( count( $groups ) )
            {
                foreach($groups as $group)
                {
                    $members[] = (string)$group['id'];
                }
            }
        }

        $statement = $this->dbService->createQueryBuilder();

        $result = $statement->select('r')
            ->from('SocialNetwork\API\Entity\Role', 'r')
            ->join('SocialNetwork\API\Entity\RoleMember', 'rm', 'WITH', "rm.roleUid = r.uid")
            ->where($statement->expr()->in('rm.memberUid', $members))
            ->getQuery()
            ->getArrayResult();

        if( empty( $result ) )
        {
            $this->logger->info( sprintf('User roles not found %s', $user ));
            return array();
        }

        return $result;
    }

    /**
     * Return all Roles of the group
     *
     * @param $user User uid
     * @param array $attributes Attributes to return
     * @return array
     */
    public function getGroupRoles($user, array $attributes = array())
    {
        $members = array( $user );
        $statement = $this->dbService->createQueryBuilder();

        $result = $statement->select('r')
            ->from('SocialNetwork\API\Entity\Role', 'r')
            ->join('SocialNetwork\API\Entity\RoleMember', 'rm', 'WITH', "rm.roleUid = r.uid")
            ->where($statement->expr()->in('rm.memberUid', $members))
            ->getQuery()
            ->getArrayResult();

        if( empty( $result ) )
        {
            $this->logger->info( sprintf('Group roles not found %s', $user ));
            return array();
        }

        return $result;
    }

    /**
     * Remove user of role
     *
     * @param $user User uid
     * @param $role Role uid
     * @return bool
     */
    public function deleteUserRole($user, $role)
    {
        $result = $this->dbService->createQueryBuilder()
            ->delete('SocialNetwork\API\Entity\RoleMember', 'rm' )
            ->where('rm.memberUid = ?1')
            ->andWhere('rm.roleUid = ?2')
            ->setParameter(1 , $user )
            ->setParameter(2 , $role )
            ->getQuery()
            ->execute() > 0 ? true : false;

        if( !$result ) $this->logger->info( sprintf('Error in remove user role %s', $user ));

        return $result;
    }

    /**
     * Remove group of role
     *
     * @param $group Group uid
     * @param $role Role uid
     * @return bool
     */
    public function deleteGroupRole($group, $role)
    {
        $result = $this->dbService->createQueryBuilder()
            ->delete('SocialNetwork\API\Entity\RoleMember', 'rm' )
            ->where('rm.memberUid = ?1')
            ->andWhere('rm.roleUid = ?2')
            ->setParameter(1 , $group )
            ->setParameter(2 , $role )
            ->getQuery()
            ->execute() > 0 ? true : false;

        if( !$result ) $this->logger->info( sprintf('Error in remove group role %s', $group ));
        return $result;
    }
}