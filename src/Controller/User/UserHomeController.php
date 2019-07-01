<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserHomeController extends AbstractController
{
    /**
     * @Route("/user/home", name="user_home")
     */
    public function index()
    {
        return $this->render('user/user_home/index.html.twig', [
            'controller_name' => 'UserHomeController',
        ]);
    }
}
