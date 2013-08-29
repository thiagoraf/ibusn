<?php

namespace SocialNetwork\Bundle\FriendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\FriendBundle\Entity\Friends;

class FriendController extends Controller
{
    /*
     * TODO: FAZER TODAS AS VALIDAÇÕES NECESSÁRIAS
     * */
    public function indexAction($name)
    {
        return $this->render('SocialNetworkFriendBundle:Default:index.html.twig', array('name' => $name));
    }

    public function addAction(){
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');

        if( $me['id'] == $params['userId'] ) {
            return $response->setData("Você não pode adicionar você mesmo!")->render();
        }

        $oMe = $dbService->find('SocialNetwork\API\Entity\User', $me["id"]);
        $oFriend = $dbService->find('SocialNetwork\API\Entity\User', $params['userId']);

        $oFriends = new Friends();
        $oFriends->setIdUserRequest( $oMe );
        $oFriends->setIdUserResponse( $oFriend );
        $oFriends->setStatus( 0 );
        $dbService->persist( $oFriends );
        $dbService->flush();

        return $response->render();
    }

    public function putAction(){
        $response = new ApiResponse();


        return $response->render();
    }

    public function friendInviteAction() {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $dbService = $this->get('doctrine.orm.entity_manager');

        $invites = $dbService->createQueryBuilder()
            ->select( 'f' )
            ->from('SocialNetwork\Bundle\FriendBundle\Entity\Friends', 'f')
            ->where("f.idUserResponse = ?1")
            ->andWhere("f.status= 0")
            ->setParameter( 1, $me['id'] )
            ->getQuery()
            ->getArrayResult();


        $friendInvite = $dbService->createQueryBuilder()
            ->select( 'ur.name, f.id' )
            //->from('SocialNetwork\API\Entity\User', 'u')
            ->from('SocialNetwork\Bundle\FriendBundle\Entity\Friends', 'f')
            ->leftJoin('f.idUserRequest', 'ur')
            ->where("f.idUserRequest = ur.id")
            ->andwhere("f.idUserResponse = ?1")
            ->andWhere("f.status = 0")
            ->setParameter( 1, $me['id'] )
            ->getQuery()
            ->getArrayResult();



        $response->setData( $friendInvite );
        return $response->render();
    }

    public function acceptFriendAction() {
        $response = new ApiResponse();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');
        $me = $this->getUser()->getAttributes();

        $oFriends = $dbService->find('SocialNetwork\Bundle\FriendBundle\Entity\Friends', $params['inviteId']);

        if( $oFriends->getIdUserResponse()->getId() != $me['id'] ) {
            return $response->setData("Erro de permissão!")->render();
        }
        $oFriends->setStatus( 1 );

        $dbService->merge( $oFriends );
        $dbService->flush();

        return $response->render();
    }

    public function declineFriendAction( $invite ) {
        $response = new ApiResponse();
        $dbService = $this->get('doctrine.orm.entity_manager');
        $me = $this->getUser()->getAttributes();

        $oFriends = $dbService->find('SocialNetwork\Bundle\FriendBundle\Entity\Friends', $invite );

        if( $oFriends->getIdUserResponse()->getId() != $me['id'] ) {
            return $response->setData("Erro de permissão!")->render();
        }

        $dbService->remove( $oFriends );
        $dbService->flush();

        return $response->render();
    }

}
