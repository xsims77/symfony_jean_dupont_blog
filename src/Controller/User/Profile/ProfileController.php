<?php

namespace App\Controller\User\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'user.profile.index')]
    public function index(): Response
    {
        return $this->render('pages/user/profile/index.html.twig');
    }
}
