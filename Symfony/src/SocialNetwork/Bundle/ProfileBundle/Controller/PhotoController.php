<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\Bundle\ProfileBundle\Entity\Photo,
    SocialNetwork\Bundle\ProfileBundle\Entity\Album;

class PhotoController extends Controller
{


    /*
     * VALIDAR SEGURANÇA AQUI!
     * */
    const PATH_USER_GET = "bundles/socialnetworkindex/users";
    const PATH_USER_POST = "../src/SocialNetwork/Bundle/IndexBundle/Resources/public/users";

    public function addAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();

        if ( $_FILES['photo']['type'] == 'image/jpeg' )
            $extension = ".jpg";
        else
            $extension = ".jpg";

        if ( move_uploaded_file( $_FILES['photo']['tmp_name'], self::PATH_USER_POST."/{$me['id']}/albums/{$_POST['album']}/".base64_encode(time().rand  ()).$extension  ) )
        {
            return $response->setData(array('success'=>'Foto inserida com sucesso!','album'=>$_POST['album'], 'user' => $me['id']))->render();
        }

        return $response->render();

    }

    public function deleteAction($album, $photo)
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();

        unlink(self::PATH_USER_POST."/{$me['id']}/albums/{$album}/{$photo}");

        return $response->render();
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

    public function addPhotoProfileAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();

        if( !file_exists(self::PATH_USER_POST . "/". $me['id']) )
            mkdir(self::PATH_USER_POST."/{$me['id']}/albums/Fotos de perfil", 0777, true);


        $path = self::PATH_USER_POST."/{$me['id']}/albums/Fotos de perfil";
        $folderProfile = opendir($path);

        while (($photoProfile = readdir($folderProfile)) !== false) {

            if( in_array( $photoProfile, array('.','..') ) )
                continue;

            if( strrpos($photoProfile, 'active_') !== false ) {
                $newName = str_replace('active_', '', $photoProfile);

                rename("{$path}/{$photoProfile}","{$path}/{$newName}");
            }

        }

        //move_uploaded_file( $_FILES['photoProfile']['tmp_name'], $path.'/active_'.$_FILES['photoProfile']['name'] );

        if(move_uploaded_file( $_FILES['photoProfile']['tmp_name'], $path.'/active_'.$_FILES['photoProfile']['name'] ))
        {
            return $response->setData(array('success'=>'Foto enviada com sucesso!'))->render();
        }

        return $response->setData(array($path.'/active_'.$_FILES['photoProfile']['name']))->render();
    }

    public function currentPhotoProfileAction()
    {
        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();

        $folderProfile = opendir( self::PATH_USER_POST . "/{$me['id']}/albums/Fotos do profile");

        while (($photoProfile = readdir($folderProfile)) !== false)
        {
            if( strrpos($photoProfile, 'active_') )
            {
                closedir($folderProfile);
                return $response->setData(array("photo" => $photoProfile))->render();
            }
        }

        closedir($folderProfile);
        return $response->render();
    }

}