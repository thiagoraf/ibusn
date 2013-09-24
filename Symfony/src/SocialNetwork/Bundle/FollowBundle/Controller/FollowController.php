<?php

namespace SocialNetwork\Bundle\FollowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\FollowBundle\Entity\Follow;

class FollowController extends Controller
{
    public function followAction(){
        try{
            $response = new ApiResponse();
            $me = $this->getUser()->getAttributes();
            $params = $this->getRequest()->request->all();
            $dbService = $this->get('doctrine.orm.entity_manager');

            if( $me['id'] == $params['userId'] ) {
                return $response->setData("Você não pode seguir você mesmo!")->render();
            }
            $oFollow = $dbService->getRepository('SocialNetwork\Bundle\FollowBundle\Entity\Follow')->findOneby( array('following' => $me['id'], 'followed' => $params['userId']) );
            if ( $oFollow && $oFollow->getId() ) {
                return $response->setData(array("error"=>"Você já seguiu este usuário!"))->render();
            }

            $oFollowing = $dbService->find('SocialNetwork\API\Entity\User', $me["id"]);
            $oFollowed = $dbService->find('SocialNetwork\API\Entity\User', $params['userId']);

            $oFollow = new Follow();
            $oFollow->setFollowing( $oFollowing );
            $oFollow->setFollowed( $oFollowed );
            $oFollow->setDatetime( time() . "000");

            $dbService->persist( $oFollow );
            $dbService->flush();

        }catch(Exception $e){

        }
        return $response->render();
    }

    public function unfollowAction( $userId ){
        try{
            $response  = new ApiResponse();
            $me        = $this->getUser()->getAttributes();
            $dbService = $this->get('doctrine.orm.entity_manager');

            $oFollow = $dbService->getRepository('SocialNetwork\Bundle\FollowBundle\Entity\Follow')->findOneby( array('following' => $me['id'], 'followed' => $userId) );

            $dbService->remove( $oFollow );
            $dbService->flush();

        }catch(Exception $e){
            $response->setData(array("error"=>$e->getMessage()));
        }
        return $response->render();
    }

    public function followingAction(){
        $response = new ApiResponse();

        $response->setData()->render();
    }

    public function followersAction(){
        $response = new ApiResponse();

        $response->setData()->render();
    }

}
