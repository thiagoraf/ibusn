<?php

namespace SocialNetwork\API\Provider;

use SocialNetwork\API\Interfaces\IRoleProvider,
    SocialNetwork\API\Helper\AttributeMap as MAP,
    SocialNetwork\API\Helper\ObjectClass as oClass,
    SocialNetwork\API\Provider\Criteria;


/**
 * Class LdapRoleProvider
 * @package SocialNetwork\API\Provider
 */
class LdapRoleProvider implements IRoleProvider
{
    protected $ldapService, $roleProvider, $map, $logger;

    /**
     * @param $container Symfony service container
     * @param array $map Map of default attributes
     */
    public function __construct( $container , $map )
    {
        $this->ldapService = $container->get('ServiceLdap');
        $this->groupProvider = $container->get('API.GroupProvider');
        $this->logger = $container->get('logger');

        $this->map = $map;
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
    public function create( $uid, $name, $description, array $members= array(), array $attributes = array())
    {
        $role = $attributes;

        $role['gidNumber'] = $this->ldapService->getNextId('role');
        $role['uidNumber'] = $role['gidNumber'];
        $role['cn'] = $name;

        if( !empty( $description ) ) $role['description'] = $description;
        if( !empty( $members ) ) $role['memberUid'] = $members;

        if(!isset( $role['homeDirectory'] )) $role['homeDirectory'] = '/home/'.$uid;

        $role['objectClass'] = oClass::get( array_change_key_case($role), 'role' );
        $this->ldapService->add( $role, 'uid='.$uid.','. $this->ldapService->getContext('role') );

        if( !(int)$role['gidNumber']  )
        {
            $this->logger->err( 'Error in create role' );
            return false;
        }

        return (int)$role['gidNumber'];
    }

    /**
     * Update exist Role
     *
     * @param $id Id of Role
     * @param $uid Uid of Role
     * @param $name New name to Role
     * @param $description New description to Role
     * @param array $members UID of the users to the group
     * @param array $attributes Custom attributes
     * @return bool
     * @throws \Exception
     */
    public function update($id, $uid, $name, $description, array $members = array(), array $attributes = array())
    {
        $role = $this->get( $id );
        $cn = $role['name'];
        $dn = $role['dn'];

        unset( $role['objectclass'] );
        unset( $role['dn'] );
        unset( $role['name'] );
        unset( $role['description'] );
        unset( $role['members'] );
        unset( $role['id'] );
        unset( $role['uid'] );
        unset( $role['uidnumber'] );
        unset( $role['homedirectory'] );

        $add = array_diff( $attributes, $role );
        $replace = array();
        $remove = array();

        if( $description ) $replace['description'] = $description;
        if( $name ) $replace['cn'] = $name;

        $replace['memberUid'] = $members;

        foreach( $role as $k => $v )
        {
            if( isset( $attributes[ $k ] ))
            {
                if(  $attributes[ $k ] === false )
                {
                    $remove[ $k ] = $v;
                }else
                {
                    $replace[ $k ] = $attributes[ $k ];
                }
            }
        }

        if( count( $add ) )
        {
            if( !$this->ldapService->addAttributes( $add, $dn ) )
            {
                $this->logger->err( sprintf('Error in add attributes on role %s', $cn) );
                return false;
            }
        }

        if( count( $remove ) )
        {
            if( !$this->ldapService->removeAttributes( $remove, $dn ) )
            {
                $this->logger->err( sprintf('Error in remove attributes on role %s', $cn) );
                return false;
            }
        }

        if( count( $replace ) )
        {
            if( !$this->ldapService->replaceAttributes( $replace, $dn ) )
            {
                $this->logger->err( sprintf('Error in replace attributes on role %s', $cn) );
                return false;
            }
        }

        if( $uid )
        {
            if( !$this->ldapService->rename( $dn, 'uid='.$uid, $this->ldapService->getContext('role') ) )
            {
                $this->logger->err( sprintf('Error in replace attributes on role %s', $cn ) );
                return false;
            }
        }

        return true;
    }

    /**
     * Read Role
     *
     * @param $id Id of Role
     * @param array $attributes Attributes to return
     * @return mixed
     * @throws \Exception
     */
    public function get( $id, array $attributes = array() )
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $filter = '(&(gidnumber='.$id.')'.$this->ldapService->getFilter('role').')';
        $role =   MAP::parser($this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('role')), $this->map );

        if( empty( $role ) )
        {
            $this->logger->info( sprintf('Role not found %u', $id ) );
            return false;
        }

        return $role[0];
    }

    /**
     * Read Role
     *
     * @param $uid uid of group
     * @param array $attributes Attributes to return
     * @return array
     * @throws \Exception
     */
    public function getByUid($uid, array $attributes = array())
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $filter = '(&(uid='.$uid.')'.$this->ldapService->getFilter('role').')';
        $role =  MAP::parser($this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('role')), $this->map);

        if( empty( $role ) )
        {
            $this->logger->info( sprintf('Role not found %s', $uid ) );
            return false;
        }

        return $role[0];
    }

    /**
     * Find Role
     *
     * @param Criteria $criteria Criteria filter
     * @param array $attributes Attributes to return
     * @return mixed
     */
    public function find( Criteria $criteria , array $attributes = array() )
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $fp = new FilterProvider( $criteria , $this->map );
        $filter =  '(&'.$fp->formatLDAP() . $this->ldapService->getFilter('role').')';

        $result = $this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('role'));

        if( empty( $result ) )
        {
            $this->logger->info('Search role without result');
            return array();
        }

        return  MAP::parser($result , $this->map );
    }

    /**
     * Delete Role
     *
     * @param $id Id of Role
     * @return mixed
     */
    public function delete($id)
    {
        $role = $this->get( $id );

        if( $role ) return $this->ldapService->delete( $role['dn'] );

        return false;
    }

    /**
     * Add role to User
     *
     * @param $user User Uid
     * @param $role Role uid
     * @return bool
     */
    public function setUserRole($user, $role)
    {
        $oRole = $this->getByUid( $role , array('dn', 'members') );

        if(!isset($oRole['members']))
        {
            $oRole['members'][] = $user;
            return $this->ldapService->addAttributes( array('memberUid' => $oRole['members']), $oRole['dn'] );
        }
        else if(!is_array($oRole['members']))
            $oRole['members'] = array($oRole['members']);

        if( !in_array( $user, $oRole['members'] ) )
        {
            $oRole['members'][] = $user;
            return $this->ldapService->replaceAttributes( array('memberUid' => $oRole['members']), $oRole['dn'] );
        }
        else
            return true;
    }

    /**
     * Add role to Group
     *
     * @param $group Group Uid
     * @param $role Role Uid
     * @return bool
     */
    public function setGroupRole($group, $role)
    {
        $oRole = $this->getByUid( $role , array('dn', 'members') );

        if(!isset($oRole['members']))
        {
            $oRole['members'][] = $group;
            return $this->ldapService->addAttributes( array('memberUid' => $oRole['members']), $oRole['dn'] );
        }
        else if(!is_array($oRole['members']))
            $oRole['members'] = array($oRole['members']);

        if( !in_array( $group, $oRole['members'] ) )
        {
            $oRole['members'][] = $group;
            return $this->ldapService->replaceAttributes( array('memberUid' => $oRole['members']), $oRole['dn'] );
        }
        else
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
        $attributes = MAP::map( $attributes , $this->map  );

        $filter = '(&'.$this->ldapService->getFilter('role').'(|(memberUid=' . $user . ')';

        if($groupRoles)
        {
            $groups = $this->groupProvider->getUserGroups( $user, array('uid') );

            if( count( $groups ) )
            {
                foreach($groups as $group)
                {
                    $filter .= '(memberUid=' . $group['uid'] . ')';
                }
            }
        }

        $filter .= '))';

        $result = $this->ldapService->search( $filter, $attributes, $this->ldapService->getContext('role') );

        if( empty( $result ) )
        {
            $this->logger->info('Search user roles without result');
            return array();
        }

        return  MAP::parser( $result , $this->map );
    }

    /**
     * Return all Roles of the group
     *
     * @param $group User uid
     * @param array $attributes Attributes to return
     * @return array
     */
    public function getGroupRoles($group, array $attributes = array())
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $filter = '(&'.$this->ldapService->getFilter('role').'(memberUid=' . $group . '))';


        $result = $this->ldapService->search( $filter, $attributes, $this->ldapService->getContext('role') );

        if( empty( $result ) )
        {
            $this->logger->info('Search group roles without result');
            return array();
        }

        return  MAP::parser( $result , $this->map );
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
        $role = $this->getByUid( $role, array('dn', 'members') );
        if( isset( $role['members'] ) && !is_array( $role['members'] ) ) $role['members'] = array( $role['members'] );

        if( isset( $role['members'] ) && ( $key = array_search( $user, $role['members']) ) !== false )
        {
            unset( $role['members'][ $key ] );
            $role['members'] = array_merge( $role['members'] , array()); //reindex

            return $this->ldapService->replaceAttributes( array('memberUid' => $role['members']), $role['dn'] );
        }
        else
            return true;
    }

    /**
     * Remove group of role
     *
     * @param $group group uid
     * @param $role Role uid
     * @return bool
     */
    public function deleteGroupRole($group, $role)
    {
        $role = $this->getByUid( $role, array('dn', 'members') );

        if( ( $key = array_search( $group, $role['members']) ) !== false )
        {
            unset( $role['members'][ $key ] );
            $role['members'] = array_merge( $role['members'] , array()); //reindex

            return $this->ldapService->replaceAttributes( array('memberUid' => $role['members']), $role['dn'] );
        }
        else
            return true;
    }
}