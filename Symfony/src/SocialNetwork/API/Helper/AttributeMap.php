<?php

namespace SocialNetwork\API\Helper;

/**
 * Class AttributeMap
 * @package SocialNetwork\API\Helper
 */
class AttributeMap
{
    /**
     * Maps default attributes passed by the controller to provider layer
     *
     * @param $attributes
     * @param $map
     * @return mixed
     */
    static function map ( $attributes  , $map)
    {
        foreach( $attributes as &$value )
        {
            if(isset($map[$value]))
                $value =  $map[$value];
        }
        return $attributes;
    }

    static function parseSend ( $attributes  , $map)
    {
        foreach( $attributes as $i => $value )
        {
            if(isset($map[$i]))
            {
                $attributes[$map[$i]] =  $value;
                unset($attributes[$i]);
            }
        }
        return $attributes;
    }



    /**
     * Map default Keys from provider layer to controller
     *
     * @param $data
     * @param $map
     * @return mixed
     */
    static function parser( $data , $map )
    {
        foreach( $data as &$value )
        {
            foreach( $map as $k => $v )
            {
                $v = strtolower($v);
                if(isset( $value[ $v ] ))
                {
                    $value[$k] = $value[$v];
                    unset( $value[$v] );
                }
            }
        }

        return $data;
    }

    /**
     *  Maps default attributes passed by the controller to provider layer (Database)
     *
     * @param $prefix Alias used in DQL
     * @param array $attributes
     * @return string
     */
    static function mapDbAttributes( $prefix , array $attributes)
    {
        if(count($attributes) > 0)
        {
            foreach($attributes as &$v)
                $v = $prefix.'.'.$v;

            return implode(', ' , $attributes);
        }
        else
            return $prefix;
    }

}