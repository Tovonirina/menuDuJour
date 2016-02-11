<?php

namespace Facebook\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Facebook\MenuBundle\Controller\FacebookMenuController;

if(!session_id()){
    session_start();
}

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

    public function showMenuAction()
    {
    	$facebook = new FacebookMenuController();

    	if( isset($_SESSION["facebook_access_token"]) && !empty($_SESSION["facebook_access_token"]) ){

    		$allMenu = $facebook->getAllMenuContent();
            
            $params = [
                "lilotregal" => $allMenu['lilotregal'],
                "regalducircuit" => $allMenu['regalducircuit']
            ];

    		return $this->render('FacebookMenuBundle:Default:menuToday.html.twig' , $params );

    	}else{
    		return $this->redirect($this->generateUrl("facebook_menu_homepage"));
    	}
        
    }
}
