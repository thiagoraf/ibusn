<?php

namespace SocialNetwork\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
SocialNetwork\Bundle\EventBundle\Entity\Event,
SocialNetwork\API\Response\ApiResponse;

class EventController extends Controller
{
    public function addAction()
    {
        $response = new ApiResponse();
	$dbService = $this->get('doctrine.orm.entity_manager');
	$request = $this->getRequest()->request->all();
		
	$oEvent = new Event();
	$oEvent->setTitle($request['title']);
	$oEvent->setDescription($request['description']);
	
	$dbService->persist($oEvent);
	$dbService->flush();

	return $response->render();
    }

    public function listAction()
    {
        $response = new ApiResponse();
	$dbService = $this->get('doctrine.orm.entity_manager');
	
	$event = $dbService->createQueryBuilder()
		->select("e")
		->from("SocialNetwork\Bundle\EventBundle\Entity\Event","e")
		->orderBy("e.id","DESC")
		->getQuery()
            	->getArrayResult();
	

	return $response->setData($event)->render();
    }

}
