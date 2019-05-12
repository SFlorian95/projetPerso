<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomepageController extends AbstractController {

    /**
     * @Route("/" , name="homepage.index")
     */
    public function index():Response {
        return $this->render('homepage/index.html.twig');

    }

}

