<?php

namespace SocialNetwork\Bundle\FriendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\FriendBundle\Entity\Friends;

class FriendController extends Controller
{
    /*
     * TODO: FAZER TODAS AS VALIDA��ES NECESS�RIAS
     * */

    public function myFriendAction()
    {
        return $this->render('SocialNetworkIndexBundle:Default:index.html.twig', array("data" => "friend"));
    }

    public function addAction(){
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');

        if( $me['id'] == $params['userId'] ) {
            return $response->setData(array("Error"=>"Você não pode adicionar você mesmo!"))->render();
        }

        $qb = $dbService->createQueryBuilder();
        $alreadyAdded = $qb->select( 'f' )
            ->from('SocialNetwork\Bundle\FriendBundle\Entity\Friends', 'f')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->eq("f.idUserResponse","?1"),
                        $qb->expr()->eq("f.idUserRequest","?2")
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->eq("f.idUserRequest","?1"),
                        $qb->expr()->eq("f.idUserResponse","?2")
                    )
                )
            )
            ->andWhere("f.status= 0")
            ->setParameter( 1, $me['id'] )
            ->setParameter( 2, $params['userId'] )
            ->getQuery()
            ->getArrayResult();

        if( !empty($alreadyAdded) ) {
            return $response->setData(array("error"=>"Uma solicitação de amizade tinha sido enviada!"))->render();
        }

        $oFriends = new Friends();
        $oFriends->setIdUserRequest( $me["id"] );
        $oFriends->setIdUserResponse( $params['userId'] );
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

        $friendInvite = $dbService->createQueryBuilder()
            ->select( 'ur.name, f.id' )
            //->from('SocialNetwork\API\Entity\User', 'u')
            ->from('SocialNetwork\Bundle\FriendBundle\Entity\Friends', 'f')
            ->leftJoin('SocialNetwork\API\Entity\User', 'ur','WITH' , 'ur.id = f.idUserRequest')
            ->andwhere("f.idUserResponse = ?1")
            ->andWhere("f.status = 0")
            ->setParameter( 1, $me['id'] )
            ->getQuery()
            ->getArrayResult();

        return $response->setData( $friendInvite )->render();
    }

    public function acceptFriendAction() {
        $response = new ApiResponse();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');
        $me = $this->getUser()->getAttributes();

        $oFriends = $dbService->find('SocialNetwork\Bundle\FriendBundle\Entity\Friends', $params['inviteId']);

        $friendInvite = $dbService->createQueryBuilder()
            ->select( 'f.idUserResponse' )
            ->from('SocialNetwork\Bundle\FriendBundle\Entity\Friends', 'f')
            ->where("f.id = ?1")
            ->setParameter( 1, $params['inviteId'] )
            ->getQuery()
            ->getSingleResult();

        if( $friendInvite['idUserResponse'] != $me['id'] ) {
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

    public function deleteFriendAction( $userId )
    {
        $response = new ApiResponse();
        $dbService = $this->get('doctrine.orm.entity_manager');
        $me = $this->getUser()->getAttributes();

        $qb = $dbService->createQueryBuilder();
        $qb->delete('SocialNetwork\Bundle\FriendBundle\Entity\Friends', 'f');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq("f.idUserResponse","?1"),
                    $qb->expr()->eq("f.idUserRequest","?2")
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq("f.idUserRequest","?1"),
                    $qb->expr()->eq("f.idUserResponse","?2")
                )
            )
        );
        $qb->setParameter(1, $me['id']);
        $qb->setParameter(2, $userId)
            ->getQuery()
            ->execute();

        return $response->render();
    }

}
