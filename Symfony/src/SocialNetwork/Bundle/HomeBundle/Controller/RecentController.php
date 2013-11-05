<?php

namespace SocialNetwork\Bundle\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	SocialNetwork\API\Response\ApiResponse;

class RecentController extends Controller
{
    public function indexAction($name)
    {
        //return $this->render('SocialNetworkHomeBundle:Default:index.html.twig', array('name' => $name));
    }

    public function newMembersAction()
    {
    	$response = new ApiResponse();
    	$dbService = $this->get('doctrine.orm.entity_manager');

         $newMembers = $dbService->createQueryBuilder()
     		->select('u.name','u.uid','u.registered')
     		->from('SocialNetwork\API\Entity\User', 'u')
     		->setMaxResults( 9 )
     		->orderBy('u.id','DESC')
     		->getQuery()
     		->getArrayResult();

    	return $response->setData( $newMembers )->render();
    }
}
