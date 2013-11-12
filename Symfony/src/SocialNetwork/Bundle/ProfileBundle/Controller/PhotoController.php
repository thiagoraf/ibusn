<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\ProfileBundle\Entity\Photo,
    SocialNetwork\Bundle\ProfileBundle\Entity\Album;

class PhotoController extends Controller
{

    public function addAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');

        $f = fopen($_FILES['photoPerfil']['tmp_name'],'r');


        return $response->setData(fgetcsv($f, 0, ';') )->render();

        if( isset( $params["album"] ) )
            $oAlbum = $dbService->find('SocialNetwork\Bundle\ProfileBundle\Entity\Album', $params["album"]);
        else {
            $album = $dbService->createQueryBuilder()
                ->select('a.id')
                ->from('SocialNetwork\Bundle\ProfileBundle\Entity\Album','a')
                ->where('a.title = ?1')
                ->setParameter(1, 'Fotos de perfil')
                ->getQuery()
                ->getArrayResult();

            if( empty($album) ) {
                $dbService2 = $this->get('doctrine.orm.entity_manager');
                $oAlbum = new Album();
                $oAlbum->setCreated(time().'000');
                $oAlbum->setOwner( $me['id'] );
                $oAlbum->setTitle("Fotos de perfil");
                $dbService2->persist( $oAlbum );
                $dbService2->flush();
            } else
                $oAlbum = $dbService->find('SocialNetwork\Bundle\ProfileBundle\Entity\Album', $album['id']);
        }

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