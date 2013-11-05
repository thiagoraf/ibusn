<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\ProfileBundle\Entity\Photo;

class PhotoController extends Controller
{

    public function addAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');

        $oAlbum = $dbService->find('SocialNetwork\Bundle\ProfileBundle\Entity\Album', $params["album"]);

        try {

            $oPhoto = new Photo();
            $oPhoto->setSource( $params['source'] ) ;
            $oPhoto->setAlbum( $oAlbum );
            $oPhoto->setTimestamp( time()."000" );

            $dbService->persist( $oPhoto );
            $dbService->flush();

        } catch(Exception $e){
            return $response->setData( array( $e->getMessage() ) )->render();
        }

        return $response->render();
    }
}