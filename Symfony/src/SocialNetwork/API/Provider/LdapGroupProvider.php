<?php

namespace SocialNetwork\API\Provider;


use SocialNetwork\API\Interfaces\IGroupProvider,
    SocialNetwork\API\Helper\AttributeMap as MAP,
    SocialNetwork\API\Helper\ObjectClass as oClass,
    SocialNetwork\API\Provider\Criteria,
    SocialNetwork\API\Provider\FilterProvider;


/**
 * Class LdapGroupProvider
 * @package SocialNetwork\API\Provider
 */
class LdapGroupProvider implements IGroupProvider
{
    protected $ldapService, $map, $logger;

    /**
     * @param $container Symfony service container
     * @param array $map Map of default attributes
     */
    public function __construct($container , $map)
    {
        $this->ldapService = $container->get('ServiceLdap');
        $this->map = $map;
        $this->logger = $container->get('logger');
    }

    /**
     * Create a new group
     *
     * @param $uid Uid to group
     * @param $name Name to group
     * @param $description Description to group
     * @param array $members Uid of the users to the group
     * @param array $attributes Custom attributes to group
     * @return bool|int
     */
    public function create( $uid , $name, $description, array $members = array(), array $attributes = array())
    {
        $group = $attributes;

        $group['gidNumber'] = $this->ldapService->getNextId('group');
        $group['uidNumber'] = $group['gidNumber'];
        $group['cn'] = $name;
        $group['uid'] = $uid;

        $context =  isset($attributes['context']) ? $attributes['context'] :  $this->ldapService->getContext('group');
        unset($attributes['context']);

        if( !empty( $description ) ) $group['description'] = $description;
        if( !empty( $members ) ) $group['memberUid'] = $members;

        if(!isset( $group['homeDirectory'] )) $group['homeDirectory'] = '/home/'.$uid;

        $group['objectClass'] = oClass::get( array_change_key_case($group), 'group' );
        $this->ldapService->add( $group, 'cn='.$name.','. $context );

        if( !(int)$group['gidNumber'] )
        {
            $this->logger->err( 'Error in create group' );
            return false;
        }

        return (int)$group['gidNumber'];
    }

    /**
     * Update exist group
     *
     * @param $id Id of group
     * @param $uid uid of group
     * @param $name New name to group
     * @param $description New Description to group
     * @param array $members UID of the users to the group
     * @param array $attributes Custom attributes
     * @return bool
     * @throws \Exception
     */
    public function update( $id, $uid, $name, $description, array $members = array(), array $attributes = array())
    {
        $group = $this->get( $id );
        $cn = $group['name'];
        $dn = $group['dn'];

        $matches = null;
        preg_match('/([^\=]+)=([^,]*),(.*)/i', $dn, $matches);
        $dnAtt = $matches[1];
        $dnContext = $matches[3];

        unset( $group['objectclass'] );
        unset( $group['dn'] );
        unset( $group['name'] );
        unset( $group['description'] );
        unset( $group['members'] );
        unset( $group['id'] );
        unset( $group['uid'] );
        unset( $group['uidnumber'] );
        unset( $group['homedirectory'] );


        unset( $attributes['id'] );
        unset( $attributes['uid'] );
        unset( $attributes['name'] );
        unset( $attributes['description'] );
        unset( $attributes['members'] );

        $add = array_diff( $attributes, $group );
        $replace = array();
        $remove = array();

        if( $description ) $replace['description'] = $description;
        if( $uid ) $replace['uid'] = $uid;

        $replace['memberUid'] = $members;

        foreach( $group as $k => $v )
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
                $this->logger->err( sprintf('Error in add attributes on group %s', $cn) );
                return false;
            }
        }

        if( count( $remove ) )
        {
            if( !$this->ldapService->removeAttributes( $remove, $dn ) )
            {
                $this->logger->err( sprintf('Error in remove attributes on group %s', $cn) );
                return false;
            }
        }

        if( count( $replace ) )
        {
            if( !$this->ldapService->replaceAttributes( $replace, $dn ) )
            {
                $this->logger->err( sprintf('Error in replace attributes on group %s', $cn) );
                return false;
            }
        }

        if( $name )
        {
            if( !$this->ldapService->rename( $dn, 'cn='.$name, $dnContext ) )
            {
                $this->logger->err( sprintf('Error in replace attributes on group %s', $cn) );
                return false;
            }
        }

        return true;
    }

    /**
     * Read Group
     *
     * @param $id Id of group
     * @param array $attributes Attributes to return
     * @return array
     * @throws \Exception
     */
    public function get($id, $attributes = array())
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $filter = '(&(gidnumber='.$id.')'.$this->ldapService->getFilter('group').')';
        $group =  MAP::parser($this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('group')), $this->map);

        if(!isset($group['members']))
            $group['members'] = array();
        else if(!is_array($group['members']))
            $group['members'] = array($group['members']);

        if( empty( $group ) )
        {
            $this->logger->info( sprintf('Group not found %u', $id ) );
            return false;
        }

        return $group[0];
    }

    /**
     * Read Role
     *
     * @param $uid Uid to role
     * @param array $attributes Attributes to return
     * @return array
     * @throws \Exception
     */
    public function getByUid( $uid , Array $attributes = array())
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $filter = '(&(uid='.$uid.')'.$this->ldapService->getFilter('group').')';
        $role =  MAP::parser($this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('group')), $this->map);

        if( empty( $role ) )
        {
            $this->logger->info( sprintf('Group not found %s', $uid ) );
            return false;
        }

        return $role[0];
    }

    /**
     * Find Groups
     *
     * @param Criteria $criteria Criteria filter
     * @param array $attributes  Attributes to return
     * @return array
     */
    public function find( Criteria $criteria, array $attributes = array() )
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $fp = new FilterProvider($criteria , $this->map );
        $filter = '(&' .$this->ldapService->getFilter('group') . $fp->formatLDAP() .')';

        $result = $this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('group'));

        if( empty( $result ) )
        {
            $this->logger->info('Search group without result');
            return array();
        }

        return MAP::parser( $result, $this->map);
    }

    /**
     * Delete Group
     *
     * @param $id Id of group
     * @return bool
     */
    public function delete($id)
    {
        $group = $this->get( $id, array( 'dn' ) );

        if( $group ) return $this->ldapService->delete( $group['dn'] );

        return false;
    }

    /**
     * Add user in group
     *
     * @param $user User uid
     * @param $group Group uid
     * @return bool
     */
    public function setUserGroup($user, $group )
    {
        $group = $this->getByUid( $group, array('dn', 'memberUid') );

        if(!isset($group['members'])) $group['members'] = array();
        else if(is_string($group['members'])) $group['members'] = array($group['members']);

        if( !in_array( $user, $group['members'] ) )
        {
            $group['members'][] = $user;
            return $this->ldapService->replaceAttributes( array('memberUid' => $group['members']), $group['dn'] );
        }
        else
            return true;
    }

    /**
     * Return all groups of the user
     *
     * @param $user User uid
     * @param array $attributes  Attributes to return
     * @return array
     */
    public function getUserGroups( $user , array $attributes = array())
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $filter = '(&(memberUid='.$user.')'.$this->ldapService->getFilter('group').')';

        $result = $this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('group') );

        if( empty( $result ) )
        {
            $this->logger->info('Search user groups without result');
            return array();
        }

        return MAP::parser( $result , $this->map);
    }

    /**
     * Remove user of group
     *
     * @param $user User uid
     * @param $group Group uid
     * @return bool
     */
    public function deleteUserGroup($user, $group)
    {
        $group = $this->getByUid( $group, array('dn', 'members') );
        if( isset( $group['members'] ) && !is_array( $group['members'] ) ) $group['members'] = array( $group['members'] );

        if( isset( $group['members'] ) &&  ( $key = array_search( $user, $group['members']) ) !== false )
        {

            unset( $group['members'][ $key ] );
            return $this->ldapService->replaceAttributes( array('memberUid' => $group['members']), $group['dn'] );

        }
        else
            return true;
    }
}