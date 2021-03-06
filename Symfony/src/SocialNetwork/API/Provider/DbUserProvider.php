<?php

namespace SocialNetwork\API\Provider;

use SocialNetwork\API\Interfaces\IUserProvider,
    SocialNetwork\API\Entity\User,
    SocialNetwork\API\Helper\AttributeMap as MAP;

/**
 * Class DbUserProvider
 * @package SocialNetwork\API\Provider
 */
class DbUserProvider implements IUserProvider
{
    /**
     * @var
     */
    private $dbService, $logger;

    /**
     * @param \SocialNetwork\API\Interfaces\Symfony $container
     * @param array $map
     */
    public function __construct($container, $map)
    {
        $this->logger = $container->get('logger');
        $this->dbService = $container->get('doctrine.orm.entity_manager');
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
        $user = new User();

        $user->setName( $name );
        $user->setUid( $uid );
        $user->setPassword( '{md5}' . base64_encode(pack("H*",md5($password))) );

        foreach($attributes as $key => $val)
        {
            $user->set{ ucfirst($key) }( $val );
        }

        $this->dbService->persist($user);
        $this->dbService->flush();

        if( !$user->getId() )
        {
            $this->logger->err( 'Error in create user' );
            return false;
        }


        return $user->getId();
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
    public function update($id , $name, $uid, $password, array $attributes = array())
    {
        $user = $this->dbService->find('SocialNetwork\API\Entity\User', $id);

        if( $user )
        {
            if( $name ) $user->setName( $name );
            if( $uid ) $user->setUid( $uid );
            if( $password ) $user->setPassword( '{md5}' . base64_encode(pack("H*",md5($password))) );

            foreach($attributes as $key => $val)
            {
                $user->set{ ucfirst($key) }( $val );
            }

            $this->dbService->merge( $user );
            $this->dbService->flush();

            if( !$user->getId() )
            {
                $this->logger->err( sprintf('Error in update user %u', $id) );
                return false;
            }

            return true;
        }else
        {
            $this->logger->info( sprintf('User not found %u', $id) );
            return false;
        }
    }

    /**
     * Read User
     *
     * @param $id Id of User
     * @param array $attributes User attributes to return
     * @return array
     * @throws \Exception
     */
    public function get($id, array $attributes = array())
    {
        $user = $this->dbService->createQueryBuilder()
            ->select( MAP::mapDbAttributes('u',$attributes) )
            ->from('SocialNetwork\API\Entity\User', 'u' )
            ->where( 'u.id = ?1' )
            ->setParameter(1 , $id )
            ->getQuery()
            ->getArrayResult();

        if( empty( $user ) )
        {
            $this->logger->info( sprintf('User not found %u', $id ));
            return false;
        }

        return $user[0];
    }

    /**
     * Find Users
     *
     * @param Criteria $criteria Criteria filter to load
     * @param array $attributes User attributes to return
     * @param mixed $context
     * @return array
     */
    public function find( Criteria $criteria , array $attributes = array() , $context = false)
    {
        $fp = new FilterProvider( $criteria );
        $fpr = $fp->formatDQL('u');

        $reuslt = $this->dbService->createQueryBuilder()
            ->select( MAP::mapDbAttributes('u' , $attributes)  )
            ->from('SocialNetwork\API\Entity\User', 'u')
            ->where($fpr['dql'])
            ->setParameters($fpr['parameters'] )
            ->getQuery()
            ->getArrayResult();

        if( empty( $reuslt ) )
        {
            $this->logger->info('User without result');
            return array();
        }

        return $reuslt;

    }

    /**
     * Read User
     *
     * @param $uid Uid of user
     * @param array $attributes User attributes to return
     * @return array
     * @throws \Exception
     */
    public function getByUid($uid, array $attributes = array() )
    {
        $user = $this->dbService->createQueryBuilder()
            ->select( MAP::mapDbAttributes('u',$attributes) )
            ->from( 'SocialNetwork\API\Entity\User', 'u' )
            ->where( 'u.uid = ?1' )
            ->setParameter(1 , $uid , \PDO::PARAM_STR)
            ->getQuery()
            ->getArrayResult();

        if( empty( $user ) )
        {
            $this->logger->info( sprintf('User not found %s', $uid ));
            return array();
        }

        return $user[0];
    }

    /**
     * Read User with you password, used for user authentication
     *
     * @param $uid Uid of User
     * @return array
     */
    public function getAuthentication($uid)
    {
      return $this->getByUid( $uid );
    }

    /**
     * Delete User
     *
     * @param $id Id of User
     * @return bool
     * @throws \Exception
     */
    public function delete( $id )
    {
        $user = $this->dbService->find('SocialNetwork\API\Entity\User', $id);
        if($user)
        {
            $this->dbService->remove($user);
            $this->dbService->flush();

            if( $user->getId() )
            {
                $this->logger->info( sprintf('Error in remove user %u', $id ));
                return false;
            }

            return true;
        }else
        {
            $this->logger->info( sprintf('User not found %u', $id ));
            return false;
        }
    }
}