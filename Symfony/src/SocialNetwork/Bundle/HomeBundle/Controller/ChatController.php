<?php

namespace SocialNetwork\Bundle\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\HomeBundle\Entity\Chat;

class ChatController extends Controller
{
    public function listAction() {
        $response = new ApiResponse();
        $dbService = $this->get('doctrine.orm.entity_manager');

        $messages = $dbService->createQueryBuilder()
            ->select( 'c.datetime, c.id, c.message, u.name, u.uid' )
            ->from('SocialNetwork\Bundle\HomeBundle\Entity\Chat', 'c')
            ->leftJoin('c.userId', 'u')
            ->where("c.userId = u.id")
            ->orderBy('c.id','DESC')
            ->setMaxResults( 30 )
            ->getQuery()
            ->getArrayResult();

        return $response->setData($messages)->render();
    }

    public function postAction() {
        $response = new ApiResponse();
        $dbService = $this->get('doctrine.orm.entity_manager');
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();

        if ( empty ( $params['message'] ) ) {
            return $response->setData(array("error"=>"A mensagem não pode ser enviada em branco!"))->render();
        }

        if ( $this->spam() ) {
            return $response->setData(array("error"=>"Você não pode enviar muitas mensagens em curto prazo!"))->render();
        }

        $oMe = $dbService->find('SocialNetwork\API\Entity\User', $me["id"]);

        $oChat = new Chat();
        $oChat->setMessage( $params['message'] );
        $oChat->setDatetime( time()."000" );
        $oChat->setUserId( $oMe );

        $dbService->persist( $oChat );
        $dbService->flush();

        return $response->render();
    }

    /*
     * TODO: Implementar uma função para impedir que o spam seja realizado neste bate-papo
     * */
    public function spam() {

        return false;
        if ( "Spam detectado!" ) {
            return true;
        }
    }

    public function deleteAction() {

    }

    public function putAction() {

    }


    /*
     * TODO: Implementar para curtir um post!
     * */
    public function likePostAction() {

    }

}
