<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\ProfileBundle\Entity\Photo,
    SocialNetwork\Bundle\ProfileBundle\Entity\Album;

class PhotoController extends Controller
{

    const PATH_USER_GET = "bundles/socialnetworkindex/users";
    const PATH_USER_POST = "../src/SocialNetwork/Bundle/IndexBundle/Resources/public/users";

    public function addAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();

        if ( $_FILES['photo']['type'] == 'image/jpeg' )
            $extension = ".jpg";

        if ( move_uploaded_file( $_FILES['photo']['tmp_name'], self::PATH_USER_POST."/{$me['id']}/albums/{$_POST['album']}/".base64_encode(time().rand  ()).$extension  ) )
        {
            return $response->setData(array('success'=>'Foto inserida com sucesso!','album'=>$_POST['album'], 'user' => $me['id']))->render();
        }




        /*$dbService = $this->get('doctrine.orm.entity_manager');

        $f = fopen($_FILES['photoPerfil']['tmp_name'],'r');

;

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
        }*/


    }

    public function listPhotoByAlbumAction( $albumName , $userId )
    {
        $response = new ApiResponse();

        $album = opendir(self::PATH_USER_GET."/{$userId}/albums/{$albumName}");

        $listPhotos = array();

        while (($photo = readdir($album)) !== false) {
            if( in_array( $photo, array('.','..') ) )
                continue;

            $listPhotos[] = array('name' => $photo, 'album' => $albumName, 'userId' => $userId);
        }

        closedir($album);


        return $response->setData($listPhotos)->render();
    }





}