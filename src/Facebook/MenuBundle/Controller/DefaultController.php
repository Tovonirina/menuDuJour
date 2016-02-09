<?php

namespace Facebook\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FacebookMenuBundle:Default:index.html.twig');
    }
}
