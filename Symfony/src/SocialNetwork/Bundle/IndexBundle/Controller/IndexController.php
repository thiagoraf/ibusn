<?php

namespace SocialNetwork\Bundle\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse;

class IndexController extends Controller
{
    public function indexAction()
    {
        return $this->render('SocialNetworkIndexBundle:Default:index.html.twig', array("data" => ""));
    }
}
