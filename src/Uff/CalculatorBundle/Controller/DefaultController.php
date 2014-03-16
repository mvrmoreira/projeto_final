<?php

namespace Uff\CalculatorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('UffCalculatorBundle:Default:index.html.twig', array('name' => $name));
    }
}
