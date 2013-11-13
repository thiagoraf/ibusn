<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\ProfileBundle\Entity\Album;

class AlbumController extends Controller
{

    const PATH_USER_GET = "bundles/socialnetworkindex/users";
    const PATH_USER_POST = "../src/SocialNetwork/Bundle/IndexBundle/Resources/public/users";

    public function addAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();
        //$dbService = $this->get('doctrine.orm.entity_manager');

        //$oOwner = $dbService->find('SocialNetwork\API\Entity\User', $me["id"]);


        $album = "/{$me['id']}/albums/{$params['album']}";

        if( file_exists( self::PATH_USER_GET.$album ) )
        {
            return $response->setData(array("error"=>"Album já existente!"))->render();
        }
        if ( mkdir( self::PATH_USER_POST.$album, 0777, true) )
        {
            return $response->setData(array("Criado com sucesso!"))->render();
        }

        /*try {

            $oAlbum = new Album();
            $oAlbum->setTitle( $params['album'] );
            $oAlbum->setDescription("") ;
            $oAlbum->setOwner( $oOwner );
            $oAlbum->setCreated( time()."000" );

            $dbService->persist( $oAlbum );
            $dbService->flush();

        } catch(Exception $e){
            return $response->setData( array( $e->getMessage() ) )->render();
        }*/

        return $response->render();
    }




}