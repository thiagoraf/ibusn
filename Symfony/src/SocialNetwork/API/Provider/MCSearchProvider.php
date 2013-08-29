<?php

namespace SocialNetwork\API\Provider;

use SocialNetwork\API\Interfaces\ISearchProvider;

/**
 * Class MCSearchProvider
 * @package SocialNetwork\API\Provider
 */
class MCSearchProvider implements ISearchProvider
{
    private $mc;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->mc = $container->get('ServiceMemcache');
    }

    /**
     * Create a new Search
     *
     * @param $data Search data
     * @param int $lifeTime lifetime in seconds
     * @return String id
     */
    public function create( $data, $lifeTime = 3600)
    {
        $token = $this->generateToken(16);

        return $this->mc->set($token , $data , $lifeTime) ? $token : false ;
    }

    /**
     * Return search data by id
     *
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->mc->get( $id );
    }

    private function generateToken($length)
    {
        $random= "";
        srand((double)microtime()*1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";

        for($i = 0; $i < $length; $i++)
        {
            $random .= substr($char_list,(rand()%(strlen($char_list))), 1);
        }

        return $random;
    }

}