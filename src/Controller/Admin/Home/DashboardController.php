<?php

namespace App\Controller\Admin\Home;

use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ContactRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/home/dashboard', name: 'admin.home.dashboard')]
    public function index(
        ContactRepository $contactRepository, 
        PostRepository $postRepository, 
        UserRepository $userRepository, 
        CommentRepository $commentRepository, 
        TagRepository $tagRepository, 
        CategoryRepository $categoryRepository, 
        PostLikeRepository $postLikeRepository): Response
    {
        return $this->render('pages/admin/home/index.html.twig', [
            'contacts'      => $contactRepository->findAll(),
            'posts'         => $postRepository->findAll(),
            'categories'    => $categoryRepository->findAll(),
            'comments'      => $commentRepository->findAll(),
            'tags'          => $tagRepository->findAll(),
            'postLikes'     => $postLikeRepository->findAll(),
            'users'         => $userRepository->findAll(),
        ]);
    }
}
