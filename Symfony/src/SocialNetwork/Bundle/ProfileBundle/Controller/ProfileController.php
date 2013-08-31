<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use SocialNetwork\Bundle\ProfileBundle\Entity\Visitors;
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse;

class ProfileController extends Controller
{
    //$oUser = $dbService->getRepository('SocialNetwork\API\Entity\User')->findBy( array("id" => $uid));
    public function user( $uid ) {
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

                if( $me['uid'] == $uid)
                    $user['visitors'] = $dbService->createQueryBuilder()
                        ->select( 'v', 'u.uid','u.name' )
                        ->from('SocialNetwork\Bundle\ProfileBundle\Entity\Visitors', 'v')
                        ->leftJoin('v.visitor', 'u')
                        ->where("v.visited = ?1")
                        ->andWhere("v.date > ?2")
                        ->setParameter( 1, $me['id'] )
                        ->setParameter( 2, (time() - 604800) * 1000 )
                        ->groupBy('u.uid')
                        ->orderBy('u.id','DESC')
                        ->getQuery()
                        ->getArrayResult();


                $user['following'] = empty( $following ) ? false : true;
            }

            return $user;

        } catch(Exception $e){
            //error
        }
    }

    public function visitorAction() {

        $response = new ApiResponse();
        $me = $this->getUser()->getAttributes();
        $params = $this->getRequest()->request->all();
        $dbService = $this->get('doctrine.orm.entity_manager');

        if ( $me['uid'] == $params['visited'] ) {
            return $response->render();
        }

        $user = $dbService->createQueryBuilder()
            ->select( 'u' )
            ->from('SocialNetwork\API\Entity\User', 'u')
            ->where("u.uid = ?1")
            ->setParameter( 1, $params['visited'] )
            ->getQuery()
            ->getArrayResult();

        $oVisitor = $dbService->find('SocialNetwork\API\Entity\User', $me["id"]);
        $oVisited = $dbService->find('SocialNetwork\API\Entity\User', $user[0]['id']);

        $oVisitors = new Visitors();
        $oVisitors->setVisitor( $oVisitor );
        $oVisitors->setVisited( $oVisited );
        $oVisitors->setDate( time()."000" );

        $dbService->persist( $oVisitors );
        $dbService->flush();

        return $response->render();
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
