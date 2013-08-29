<?php

namespace SocialNetwork\API\Provider;

use SocialNetwork\API\Interfaces\IUserProvider,
    SocialNetwork\API\Helper\AttributeMap as MAP,
    SocialNetwork\API\Helper\ObjectClass as oClass,
    SocialNetwork\API\Provider\Criteria;

/**
 * Class LdapUserProvider
 * @package SocialNetwork\API\Provider
 */
class LdapUserProvider implements IUserProvider
{
    protected $ldapService, $map, $logger;

    /**
     * @param $container Symfony service container
     * @param array $map Map of default attributes
     */
    public function __construct( $container, $map )
    {
        $this->map = $map;
        $this->logger = $container->get('logger');
        $this->ldapService = $container->get('ServiceLdap');
    }

    /**
     * Create new User
     *
     * @param $name Name to user
     * @param $uid Uid name to user
     * @param $password Password to user
     * @param array $attributes Custom attributes on user
     * @return bool|int
     */
    public function create( $name, $uid, $password, array $attributes = array())
    {
        $user = $attributes;

        $context =  isset($attributes['context']) ? $attributes['context'] :  $this->ldapService->getContext('user');
        unset($user['context']);

        $user['uidNumber'] = $this->ldapService->getNextId('user');
        $user['cn'] = $name;
        $user['uid'] = $uid;
        $user['userpassword'] = '{md5}' . base64_encode(pack("H*",md5($password)));

        if(!isset( $user['gidNumber'] )) $user['gidNumber'] = 1;
        if(!isset( $user['homeDirectory'] )) $user['homeDirectory'] = '/home/'.$uid;

        $user['objectclass'] = oClass::get(array_change_key_case( $user, CASE_LOWER ), 'user');

        $user = MAP::parseSend( $user , $this->map );

        $this->ldapService->add( $user, 'uid='.$uid.','. $context );

        if( !(int)$user['uidNumber'] )
        {
            $this->logger->err( 'Error in create user' );
            return false;
        }

        return (int)$user['uidNumber'];
    }

    /**
     * Update exist User
     *
     * @param $id ID of User
     * @param $name new name to replace
     * @param $uid new uidname to replace
     * @param $password new password to replace
     * @param array $attributes Custom attributes to replace
     * @return bool
     * @throws \Exception
     */
    public function update($id, $name, $uid, $password, array $attributes = array())
    {
        $attributes = array_change_key_case(  $attributes , CASE_LOWER );
        $user = $this->get( $id );

        $dn = $user['dn'];
        $oldUid = $user['uid'];

        $matches = null;
        preg_match('/([^\=]+)=([^,]*),(.*)/i', $dn, $matches);
        $dnAtt = $matches[1];
        $dnContext = $matches[3];

        $context = false;
        if(isset($attributes['context'])) $context = $attributes['context'];

        unset( $user['id'] );
        unset( $user['name'] );
        unset( $user['dn'] );
        unset( $user['uid'] );
        unset( $user['userpassword'] );
        unset( $user['homedirectory'] );
        unset( $attributes['context'] );

        $add = array_diff_key( $attributes, $user );

        foreach($add as $i => $g)
        {
            if($g === false)
                unset($add[$i]);
        }

        $add['objectclass'] = array_diff( oClass::get( $add, 'user'), array_map('strtolower', $user['objectclass']));

        if(empty( $add['objectclass'] ) )
            unset( $add['objectclass'] );
        else
            $add['objectclass']  = array_values( $add['objectclass'] );


        $replace = array();
        $remove = array();

        if( $name && $dnAtt !== 'cn' ) $replace['cn'] = $name;
        if( $uid && $dnAtt !== 'uid' ) $replace['uid'] = $uid;

        if( $password ) $replace['userpassword'] = '{md5}' . base64_encode(pack("H*",md5($password)));

        foreach( $user as $k => $v )
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
            if( !$this->ldapService->addAttributes( MAP::parseSend( $add , $this->map ), $dn ) )
            {
                $this->logger->err( sprintf('Error in add attributes on user %s', $oldUid) );
                return false;
            }
        }

        if( count( $replace ) )
        {
            if( !$this->ldapService->replaceAttributes( MAP::parseSend( $replace , $this->map ), $dn ) )
            {
                $this->logger->err( sprintf('Error in replace attributes on user %s', $oldUid) );
                return false;
            }
        }

        if( count( $remove ) )
        {
            if( !$this->ldapService->removeAttributes(  MAP::parseSend( $remove , $this->map ), $dn ) )
            {
                $this->logger->err( sprintf('Error in remove attributes on user %s', $oldUid) );
                return false;
            }
        }

        $dnAttVal = ($dnAtt == 'uid') ? $uid : $name;
        if( !$this->ldapService->rename( $dn, "$dnAtt=".$dnAttVal, $dnContext ) )
        {
            $this->logger->err( sprintf('Error in replace uid on user %s', $oldUid) );
            return false;
        }

        if($context AND $dnContext != $context)
        {
            $uid = $uid ? $uid : $oldUid;
            if( !$this->ldapService->rename( $dn, "uid=".$uid, $context ) )
            {
                $this->logger->err( sprintf('Error in replace context user %s', $oldUid) );
                return false;
            }
        }

        return true;
    }

    /**
     * Read User
     *
     * @param $id Id of User
     * @param array $attributes User attributes to return
     * @return mixed
     * @throws \Exception
     */
    public function get($id, array $attributes = array() )
    {
        $attributes = MAP::map( $attributes , $this->map  );

        $filter = '(&(uidnumber='.$id.')'.$this->ldapService->getFilter('user').')';
        $user =  MAP::parser($this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('user')), $this->map);

        if( empty( $user ) )
        {
            $this->logger->info( sprintf('User not found %u', $id ));
            return false;
        }

        return $user[0];
    }

    /**
     * Read User
     *
     * @param $uid Uid of user
     * @param array $attributes User attributes to return
     * @return mixed
     * @throws \Exception
     */
    public function getByUid($uid, array $attributes = array() )
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $filter = '(&(uid='.$uid.')'.$this->ldapService->getFilter('user').')';
        $user =  MAP::parser($this->ldapService->search( $filter , $attributes , $this->ldapService->getContext('user')), $this->map);

        if( empty( $user ) )
        {
            $this->logger->info( sprintf('User not found %s', $uid ));
            return false;
        }

        return $user[0];
    }

    /**
     * Read User with you password, used for user authentication
     *
     * @param $uid Uid of User
     * @return mixed
     * @throws \Exception
     */
    public function getAuthentication($uid)
    {
        $this->ldapService->bindAdmin();
        $filter = '(&(uid='.$uid.')'.$this->ldapService->getFilter('user').')';
        $user =  MAP::parser($this->ldapService->search( $filter , array() , $this->ldapService->getContext('user')), $this->map);
        $this->ldapService->unbindAdmin();

        if( empty( $user ) )
        {
            $this->logger->info('User without result');
            return array();
        }

        return $user[0];
    }

    /**
     * Find Users
     *
     * @param Criteria $criteria Criteria filter to load
     * @param array $attributes User attributes to return
     * @param mixed $context
     * @return mixed
     */
    public function find( Criteria $criteria , array $attributes = array() , $context = false )
    {
        $attributes = MAP::map( $attributes , $this->map  );
        $fp = new FilterProvider( $criteria , $this->map );
        $filter = '(&' .$this->ldapService->getFilter('user') . $fp->formatLDAP() .')';
        $ldapContext = $context ? $context : $this->ldapService->getContext('user');

        $result = $this->ldapService->search( $filter , $attributes , $ldapContext );

        if( empty( $result ) )
        {
            $this->logger->info('User without result');
            return array();
        }

        return MAP::parser( $result , $this->map );
    }

    /**
     * Delete User
     *
     * @param $id Id of User
     * @return mixed
     */
    public function delete($id)
    {
        $user = $this->get( $id, array( 'dn' ) );

        if( empty( $user ) ) return false;
        return $this->ldapService->delete( $user['dn'] );
    }
}