<?php

namespace SocialNetwork\Bundle\LoginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {
        return $this->render('SocialNetworkLoginBundle:Login:index.html.twig');
    }

    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

}
