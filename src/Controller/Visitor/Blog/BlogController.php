<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Post;
use App\Repository\TagRepository;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    #[Route('/blog/all-posts', name: 'visitor.blog.index')]
    public function index(CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository): Response
    {
        $categories = $categoryRepository->findAll();

        $tags = $tagRepository->findAll();

        $posts = $postRepository->findBy(['isPublished'=> true], ['publishedAt' => 'DESC']);

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags'       => $tags,
            'posts'      => $posts,
        ]);
    }

    #[Route('/blog/post/{id}/{slug}', name: 'visitor.blog.post.show', methods:['GET'])]
    public function show(Post $post) : Response
    {
        return $this->render("pages/visitor/blog/show.html.twig", [
            "post" => $post
        ]);
    }
}
