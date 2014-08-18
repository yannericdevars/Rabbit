<?php

namespace DW\RabbitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DWRabbitBundle:Default:index.html.twig', array('name' => $name));
    }
}
