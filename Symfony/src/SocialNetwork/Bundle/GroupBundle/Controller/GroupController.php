<?php

namespace SocialNetwork\Bundle\GroupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GroupController extends Controller
{
    public function indexAction( $groupId )
    {
        return $this->render('SocialNetworkIndexBundle:Default:index.html.twig', array("data" => "group/$groupId"));
    }
}
