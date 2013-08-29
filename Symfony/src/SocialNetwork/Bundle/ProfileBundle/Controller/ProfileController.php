<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse;

class ProfileController extends Controller
{
    //$oUser = $dbService->getRepository('SocialNetwork\API\Entity\User')->findBy( array("id" => $uid));
    public function user( $uid ){
        try {
            $me = $this->getUser()->getAttributes();
            $dbService = $this->get('doctrine.orm.entity_manager');

            $user = $dbService->createQueryBuilder()
                ->select( 'u','f')
                ->from('SocialNetwork\API\Entity\User', 'u')
                ->leftJoin('u.follow', 'f')
                //->leftJoin('f.following', 'uu')
                ->where("u.uid = ?1")
                ->setParameter( 1, $uid )
                ->getQuery()
                ->getArrayResult();

            if ( !empty ( $user ) ) {
                $user = $user[0];
                unset( $user['password'] );

                $following = $dbService->createQueryBuilder()
                    ->select( 'f' )
                    ->from('SocialNetwork\Bundle\FollowBundle\Entity\Follow', 'f')
                    ->where("f.followed  = ?1")
                    ->andWhere("f.following = ?2")
                    ->setParameter( 1, $user['id'] )
                    ->setParameter( 2, $me['id'] )
                    ->getQuery()
                    ->getArrayResult();

                $user['following'] = empty( $following ) ? false : true;
            }

            return $user;

        } catch(Exception $e){
            //error
        }
    }

    public function indexAction( $uid )
    {
        return $this->render('SocialNetworkIndexBundle:Default:index.html.twig', array("data" => "profile/$uid"));
    }

    public function userAction( $uid )
    {
        $response = new ApiResponse();

        $user = $this->user( $uid );

        $response->setData( $user );
        return $response->render();
    }

}
