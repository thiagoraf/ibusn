<?php

namespace SocialNetwork\API\Interfaces;

/**
 * Class ISearchProvider
 * @package SocialNetwork\API\Interfaces
 */
interface ISearchProvider
{
    /**
     * @param $container
     */
    public function __construct( $container );

    /**
     * Create a new Search
     *
     * @param $data Search data
     * @param int $lifeTime lifetime in seconds
     * @return String id
     */
    public function create(  $data , $lifeTime = 3600 );

    /**
     * Return search data by id
     *
     * @param $id
     * @return mixed
     */
    public function get($id);


}