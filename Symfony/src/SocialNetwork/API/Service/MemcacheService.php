<?php

namespace SocialNetwork\API\Service;

/**
 * Class MemcacheService
 * @package SocialNetwork\API\Service
 */
class MemcacheService extends \Memcache
{
    var $expiration;

    /**
     * Instance MemcacheService using parameters.yml
     *
     * @param int $expiration
     * @param array $servers
     */
    public function __construct( $expiration = 3600 , array $servers )
    {
        $this->expiration = $expiration;

        foreach ($servers as $server)
        {
            $this->addserver( $server['host'] , $server['port']);
        }
    }

    /**
     * tores an item var with key on the memcached server. Parameter expire is expiration time in seconds.
     * If it's 0, the item never expires (but memcached server doesn't guarantee this item to be stored all the time,
     * it could be deleted from the cache to make place for other items).
     * You can use MEMCACHE_COMPRESSED constant as flag value if you want to use on-the-fly compression (uses zlib).
     * @param string $key The key that will be associated with the item.
     * @param mixed $var The variable to store. Strings and integers are stored as is, other types are stored serialized.
     * @param int $flag [optional] Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib).
     * @param int $expire [optional] Expiration time of the item. If it's equal to zero, the item will never expire.
     * You can also use Unix timestamp or a number of seconds starting from current time,
     * but in the latter case the number of seconds may not exceed 2592000 (30 days).
     * @return bool Returns TRUE on success or FALSE on failure. Returns FALSE on failure.
     * @link http://www.php.net/manual/en/memcache.set.php
     */
    public function set($token , $data )
    {
        return parent::set($token , $data, ( is_bool( $data ) || is_int( $data ) || is_float( $data ) ) ? false : MEMCACHE_COMPRESSED, $this->expiration ) ;
    }
}