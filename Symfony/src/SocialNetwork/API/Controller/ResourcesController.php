<?php

namespace SocialNetwork\API\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Response,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\API\Entity\User;

/**
 * Class ResourcesController
 * @package SocialNetwork\API\Controller
 */
class ResourcesController extends Controller
{

    /**
     * Load EJS file and parse the twig syntax to translate
     *
     * @param String $ejs
     * @return template
     */
    public function getEJSAction( $ejs )
    {
        preg_match('/([^\.]+)\.(.+)/' , $ejs , $matches );
        $bundle = $matches[1] == 'API' ? $matches[1] : $matches[1] . 'Bundle';

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->setMaxAge(3600);
        $response->setSharedMaxAge(3600);
        $response->setPublic();

        return $this->render( 'SocialNetwork'.$bundle.':EJS:'.$matches[2].'.ejs.twig', array() , $response );
    }

    /**
     * Load JS file and parse the twig syntax to translate
     *
     * @param String $js
     * @return script
     */
    public function getJSAction( $js )
    {
        preg_match('/([^\.]+)\.(.+)/' , $js , $matches );
        $bundle = $matches[1] == 'API' ? $matches[1] : $matches[1] . 'Bundle';

        $response = new Response();
        $response->headers->set('Content-Type', 'text/javascript');
        $response->setMaxAge(3600);
        $response->setSharedMaxAge(3600);
        $response->setPublic();

        return $this->render( 'SocialNetwork'.$bundle.':JS:'.$matches[2].'.js.twig', array() , $response );
    }

    public function userMeAction()
    {
        $response = new ApiResponse();
        $oUser =  $this->getUser();
        if(!$oUser)
        {
            return $response->render();
        }

        if( method_exists( $oUser, 'getAttributes' ) )
        {
            $user = $oUser->getAttributes();
            unset($user['password']);
            unset($user['objectclass']);

        }else{

            $user = array();
            $user['uid'] = $oUser->getUsername();
        }

        $user['roles'] =  $oUser->getRoles();
        $response->setData( $user );
        return $response->render();
    }

    public function addUserAction()
    {
        $response = new ApiResponse();
        $dbService = $this->get('doctrine.orm.entity_manager');
        $user = $this->getRequest()->request->all();

        $oUser = new User();
        $oUser->setUid( $user['username'] );
        $oUser->setName( $user['name'] );
        $oUser->setPassword( md5( $user['password'] ) );
        $oUser->setRegistered( time().'000' );

        $dbService->persist( $oUser );
        $dbService->flush();

        return $response->render();
    }

}