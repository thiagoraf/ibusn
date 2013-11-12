<?php

namespace SocialNetwork\Bundle\ProfileBundle\Controller;

use SocialNetwork\Bundle\ProfileBundle\Entity\Visitors;
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    SocialNetwork\API\Entity\User;

class ProfileController extends Controller
{

    public function indexAction( $uid )
    {
        return $this->render('SocialNetworkIndexBundle:Default:index.html.twig', array("data" => "profile/$uid"));
    }

    //$oUser = $dbService->getRepository('SocialNetwork\API\Entity\User')->findBy( array("id" => $uid));
    public function userAction( $uid )
    {
        try {
            $response = new ApiResponse();
            $me = $this->getUser()->getAttributes();
            $dbService = $this->get('doctrine.orm.entity_manager');

            $user = $dbService->createQueryBuilder()
                ->select( 'u' )
                ->from('SocialNetwork\API\Entity\User', 'u')
                //->leftJoin('f.following', 'uu')
                ->where("u.uid = ?1")
                ->setParameter( 1, $uid )
                ->getQuery()
                ->getArrayResult();

            if ( !empty ( $user ) ) {
                $user = $user[0];
                unset( $user['password'] );

                $user['album'] = $dbService->createQueryBuilder()
                    ->select( 'a' )
                    ->from('SocialNetwork\Bundle\ProfileBundle\Entity\Album', 'a')
                    ->leftJoin('a.owner', 'u')
                    ->orderBy('a.id','DESC')
                    ->getQuery()
                    ->getArrayResult();

                $qb = $dbService->createQueryBuilder();
                $user['friend'] = $qb->select( 'u.name, u.uid' )
                    ->from('SocialNetwork\Bundle\FriendBundle\Entity\Friends', 'f')
                    ->join('SocialNetwork\API\Entity\User','u', 'WITH', "u.id = f.idUserResponse OR u.id = f.idUserRequest")
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->orX(
                                $qb->expr()->eq("f.idUserResponse", "?1"),
                                $qb->expr()->eq("f.idUserRequest", "?1")
                            )
                        ),
                        $qb->expr()->andX(
                                $qb->expr()->neq("u.id","?1")
                        )
                    )
                    ->andWhere("f.status = 1")
                    ->setParameter( 1, $user['id'] )
                    ->getQuery()
                    ->getArrayResult();

                if( $me['uid'] == $uid)
                    $user['visitors'] = $dbService->createQueryBuilder()
                        ->select( 'v', 'u.uid','u.name' )
                        ->from('SocialNetwork\Bundle\ProfileBundle\Entity\Visitors', 'v')
                        ->join('SocialNetwork\API\Entity\User', 'u', 'WITH', 'u.id = v.visitor')
                        ->where("v.visited = ?1")
                        ->andWhere("v.date > ?2")
                        ->setParameter( 1, $me['id'] )
                        ->setParameter( 2, (time() - 604800) * 1000 )
                        ->groupBy('u.uid')
                        ->orderBy('u.id','DESC')
                        ->getQuery()
                        ->getArrayResult();

                $qb = $dbService->createQueryBuilder();
                $isFriend = $qb->select( 'f' )
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
                    ->setParameter( 2, $user['id'] )
                    ->getQuery()
                    ->getArrayResult();

                //$user['following'] = empty( $following ) ? false : true;
                $user['isFriend'] = empty( $isFriend ) ? false : true;
            }

            return $response->setData($user)->render();

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
            ->select( 'u.id' )
            ->from('SocialNetwork\API\Entity\User', 'u')
            ->where("u.uid = ?1")
            ->setParameter( 1, $params['visited'] )
            ->getQuery()
            ->getSingleResult();


        $oVisitors = new Visitors();
        $oVisitors->setVisitor( $me["id"] );
        $oVisitors->setVisited( $user['id'] );
        $oVisitors->setDate( time()."000" );

        $dbService->persist( $oVisitors );
        $dbService->flush();

        return $response->render();
    }


}
