<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\TagRepository;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/blog/post/{id}/{slug}', name: 'visitor.blog.post.show', methods:['GET', 'POST'])]
    public function show(Post $post, Request $request, EntityManagerInterface $em) : Response
    {

        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $comment->setPost($post);
            $comment->setUser($this->getUser());

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('visitor.blog.post.show' ,[
                "id"    => $post->getId(),
                "slug"  => $post->getSlug(),
            ]);
        }

        return $this->render("pages/visitor/blog/show.html.twig", [
            "post" => $post,
            "form" => $form->createView()
        ]);
    }
}
