<?php

namespace App\Controller\User\Home;

use App\Repository\CommentRepository;
use App\Repository\PostLikeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/user/home', name: 'user.home.index')]
    public function index(CommentRepository $commentRepository,  PostLikeRepository $postLikeRepository): Response
    {
        
        return $this->render('pages/user/home/index.html.twig',[
            'comments'      => $commentRepository,
            'postsLikes'    => $postLikeRepository,
        ]);
    }
}
