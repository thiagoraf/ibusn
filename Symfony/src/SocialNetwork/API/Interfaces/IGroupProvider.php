<?php

namespace SocialNetwork\API\Interfaces;

use SocialNetwork\API\Provider\Criteria;

/**
 * Interface IGroupProvider
 * @package SocialNetwork\API\Interfaces
 */
interface IGroupProvider
{
    /**
     * @param $container Symfony service container
     * @param array $map Map of default attributes
     */
    public function __construct( $container , $map );

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
    public function create( $uid , $name  , $description , array $members = array() , array $attributes = array());

    /**
     * Update exist group
     *
     * @param $id Id of group
     * @param $uid Uid of group
     * @param $name New name to group
     * @param $description New Description to group
     * @param array $members UID of the users to the group
     * @param array $attributes Custom attributes
     * @return bool
     */
    public function update( $id  , $uid , $name  , $description , array $members = array() , array $attributes = array());

    /**
     * Read Group
     *
     * @param $id Id of group
     * @param array $attributes Attributes to return
     * @return array
     */
    public function get( $id, $attributes = array() );

    /**
     * Read Group
     *
     * @param $uid uid of group
     * @param array $attributes Attributes to return
     * @return array
     */
    public function getByUid( $uid, Array $attributes = array() );

    /**
     * Find Groups
     *
     * @param Criteria $criteria Criteria filter
     * @param array $attributes  Attributes to return
     * @return array
     */
    public function find(  Criteria $criteria , array $attributes = array() );

    /**
     * Delete Group
     *
     * @param $id Id of group
     * @return bool
     */
    public function delete( $id );

    /**
     * Add user in group
     *
     * @param $user User uid
     * @param $group Group uid
     * @return bool
     */
    public function setUserGroup ($user , $group );

    /**
     * Return all groups of the user
     *
     * @param $user User uid
     * @param array $attributes  Attributes to return
     * @return array
     */
    public function getUserGroups( $user , array $attributes = array());

    /**
     * Remove user of group
     *
     * @param $user User uid
     * @param $group Group uid
     * @return bool
     */
    public function deleteUserGroup( $user , $group );
}