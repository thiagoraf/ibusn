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

    const PATH_USER_GET = "bundles/socialnetworkindex/users";
    const PATH_USER_POST = "../src/SocialNetwork/Bundle/IndexBundle/Resources/public/users";

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

        $folderProfile = opendir(self::PATH_USER_POST . "/{$user['id']}/albums/Fotos de perfil");

        while (($photoProfile = readdir($folderProfile)) !== false) {

            if( strrpos($photoProfile, 'active_') !== false ) {
                $user['photoProfile'] = $photoProfile;
                break;
            }
        }

        closedir($folderProfile);

        $user['roles'] =  $oUser->getRoles();
        return $response->setData( $user )->render();
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

        if ( $userId = $oUser->getId() )
        {
            if ( !mkdir( self::PATH_USER_POST."/{$userId}/albums/Fotos de perfil", 0777, true) )
            {
                return $response->setData(array("error"=>"Algum erro ocorreu!"))->render();
            }
        }

        return $response->setData(array( $oUser->getId() ))->render();
    }


    public function editUserAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $request = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');
        $oUser = $dbService->find('SocialNetwork\API\Entity\User', $me["id"]);

        if ( $request['name'] )
        {
            if( $request['name'] == 'name' )
            {
                $oUser->setName( $request['value'] );
            } else if ( $request['name'] == 'aboutMe' )
            {
                $oUser->setAboutMe( $request['value'] );
            } else if ( $request['name'] = 'age' )
            {
                $oUser->setAge( strtotime($request['value']) . "000" );
            }

        }

        $dbService->merge( $oUser );
        $dbService->flush();

        return $response->setData($request)->render();
    }

    public function searchUserAction( $keyWord )
    {
        $response = new ApiResponse();
        $dbService = $this->get('doctrine.orm.entity_manager');

        $qb = $dbService->createQueryBuilder();
        $users = $qb->select( 'u' )
            ->from('SocialNetwork\API\Entity\User', 'u')
            ->where('u.name LIKE ?1')
            ->setParameter( 1, "%".$keyWord."%" )
            ->getQuery()
            ->getArrayResult();

        $found = array();
        foreach($users as $user) {

            $found[] = $user['uid'];

        }

        return $response->setData($found)->render();
    }

}