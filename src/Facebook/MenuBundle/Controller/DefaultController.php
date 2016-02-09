<?php

namespace Facebook\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Facebook\MenuBundle\Controller\FacebookMenuController;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$facebook = new FacebookMenuController();

        $loginUrl = $facebook->getLoginUrl();
        $params = [
            "Title" => "Connexion à Facebook",
            "loginUrl" => $loginUrl
        ];
        return $this->render('FacebookMenuBundle:Default:login.html.twig', $params);
    }
}
