<?php

namespace SocialNetwork\Bundle\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    SocialNetwork\API\Response\ApiResponse,
    Symfony\Component\HttpFoundation\Session\Session;

class IndexController extends Controller
{
    public function indexAction()
    {
        $session = new Session();
        //$session->set('_locate','pt_BR');

        $request = $this->getRequest();
        $request->setLocale( $session->get('_locate') );
        $t = $this->get("translator");


        return $this->render('SocialNetworkIndexBundle:Default:index.html.twig', array("data" => $t->trans("symfony is great!") ));
    }
}
