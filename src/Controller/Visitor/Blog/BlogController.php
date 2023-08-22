<?php

namespace App\Controller\Visitor\Blog;

use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog/all-posts', name: 'visitor.blog.index')]
    public function index(CategoryRepository $categoryRepository, TagRepository $tagRepository): Response
    {
        $categories = $categoryRepository->findAll();

        $tags = $tagRepository->findAll();
        return $this->render('pages/visitor/blog/index.html.twig', compact('categories', 'tags'));
    }
}
