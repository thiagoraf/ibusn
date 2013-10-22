<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\ProfileBundle\Entity\Album;

class AlbumController extends Controller
{

    public function addAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');

        $oOwner = $dbService->find('SocialNetwork\API\Entity\User', $me["id"]);

        try {

            $oAlbum = new Album();
            $oAlbum->setTitle( $params['album'] );
            $oAlbum->setDescription("") ;
            $oAlbum->setOwner( $oOwner );
            $oAlbum->setCreated( time()."000" );

            $dbService->persist( $oAlbum );
            $dbService->flush();

        } catch(Exception $e){
            return $response->setData( array( $e->getMessage() ) )->render();
        }

        return $response->render();
    }
}