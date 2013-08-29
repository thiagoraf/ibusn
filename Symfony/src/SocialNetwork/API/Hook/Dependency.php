<?php

namespace SocialNetwork\API\Hook;

/**
 * Class Dependency
 * @package SocialNetwork\Bundle\InstallBundle\Controller
 */
class Dependency {

    protected $container;

    /**
     * @param $container Symfony service container
     */
    function __construct( $container )
    {
        $this->container = $container;
    }

    /**
     *  Check the dependy before install module
     *
     * @return mixed|array
     */
    function check()
    {
        $returns = array();
        $translator = $this->container->get('translator');

        if( !is_writable(  __DIR__ .  '/../../../../app/config/parameters.yml' ) )
        {
            $returns[__DIR__ .  '/../../../../app/config/parameters.yml'] = $translator->trans('is not writable');
        }

        if( !is_writable(  __DIR__ .  '/../../../../app/config/routing.yml' ) )
        {
            $returns[__DIR__ .  '/../../../../app/config/routing.yml'] = $translator->trans('is not writable');
        }

        if( !is_writable(  __DIR__ .  '/../../../../app/config/bundles.yml' ) )
        {
            $returns[__DIR__ .  '/../../../../app/config/bundles.yml'] = $translator->trans('is not writable');
        }

        return $returns;
    }
}