<?php

namespace SocialNetwork\API\Interfaces;

use SocialNetwork\API\Provider\Criteria;

/**
 * Interface IUserProvider
 * @package SocialNetwork\API\Interfaces
 */
interface IUserProvider
{
    /**
     * @param $container Symfony service container
     * @param array $map Map of default attributes
     */
    public function __construct( $container , $map );

    /**
     * Create new User
     *
     * @param $name Name to user
     * @param $uid Uid name to user
     * @param $password Password to user
     * @param array $attributes Custom attributes on user
     * @return bool|int
     */
    public function create( $name, $uid , $password, array $attributes = array());

    /**
     * Update exist User
     *
     * @param $id ID of User
     * @param $name new name to replace
     * @param $uid new uidname to replace
     * @param $password new password to replace
     * @param array $attributes Custom attributes to replace
     * @return bool
     */
    public function update( $id  , $name, $uid , $password, array $attributes = array());

    /**
     * Read User
     *
     * @param $id Id of User
     * @param array $attributes User attributes to return
     * @return array
     */
    public function get( $id, array $attributes = array() );

    /**
     * Find Users
     *
     * @param Criteria $criteria Criteria filter to load
     * @param array $attributes User attributes to return
     * @param mixed $context
     * @return array
     */
    public function find( Criteria $criteria , array $attributes = array() , $context = false);

    /**
     * Read User
     *
     * @param $uid Uid of user
     * @param array $attributes User attributes to return
     * @return array
     */
    public function getByUid($uid, array $attributes = array() );

    /**
     * Read User with you password, used for user authentication
     *
     * @param $uid Uid of User
     * @return array
     */
    public function getAuthentication( $uid );

    /**
     * Delete User
     *
     * @param $id Id of User
     * @return bool
     */
    public function delete( $id );
}