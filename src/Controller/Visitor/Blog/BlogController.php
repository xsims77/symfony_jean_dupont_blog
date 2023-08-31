<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Category;
use App\Entity\PostLike;
use App\Form\CommentFormType;
use App\Repository\TagRepository;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BlogController extends AbstractController
{
    #[Route('/blog/all-posts', name: 'visitor.blog.index')]
    public function index(CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $categories = $categoryRepository->findAll();

        $tags = $tagRepository->findAll();

        $postsPublished = $postRepository->findBy(['isPublished'=> true], ['publishedAt' => 'DESC']);

        $posts = $paginator->paginate(
            $postsPublished, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

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

    #[Route('/blog/posts/filter-by-category/{slug}', name: 'visitor.blog.posts.filter_by_category', methods:['GET'])]
    public function FilterByCategory(Category $category, CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository, PaginatorInterface $paginator, Request $request) : Response
    {
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $postsPublished = $postRepository->filterPostsByCategory($category->getId());

        $posts = $paginator->paginate(
            $postsPublished, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render("pages/visitor/blog/index.html.twig", [
            "categories"    => $categories,
            "tags"          => $tags,
            "posts"         =>$posts,
        ]);
    }

    #[Route('/blog/posts/filter-by-tag/{slug}', name: 'visitor.blog.posts.filter_by_tag', methods:['GET'])]
    public function filterByTag(Tag $tag, CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository, PaginatorInterface $paginator, Request $request ) : Response
    {
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $postsPublished = $postRepository->filterPostsByTag($tag->getId());

        $posts = $paginator->paginate(
            $postsPublished, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render("pages/visitor/blog/index.html.twig", [
            "categories"    => $categories,
            "tags"          => $tags,
            "posts"         => $posts,
        ]);
    }

    #[Route('/blog/post/{id<\d+>}/{slug}/like', name: 'visitor.blog.post.like', methods: ['GET'])]
    public function like(Post $post, PostLikeRepository $postLikeRepository, EntityManagerInterface $em) : Response
    {
        //Récupérons l'utilisateur censé être connecté.
        $user = $this->getUser();

        // S'il n'est pas connecté,
        if (!$user) 
        {
            // Retourne la réponse au navigateur du client, lui expliquant que l'utilisateur n'est pas connecté
            return $this->json([ 'message' => "Vous devez être connecté avant de liker cet article"], 403);
        }

        //Dans le cas contraire, 

        // Vérifions, si l'article a déjà été liké par l'utilisateur connecté,
        if ( $post->isLikedBy($user) ) 
        {

            // Récupérons ce like
            $like = $postLikeRepository->findOneBy(['post' => $post, 'user' => $user]);

            // Demandons au gestioonnaire des entités de supprimer le like.
            $em->remove($like);
            $em->flush();

            // Retournons la réponse correspondante au navigateur du client pour qu'il mette à jour les données 
            return $this->json([
                'message'       => "Le like a été retiré",
                'totalLikes'    => $postLikeRepository->count(['post' => $post])
            ]);
        }

        // Dans le cas contraire,

        // Créons le nouveau like
        $postLike = new PostLike();
        $postLike->setUser($user);
        $postLike->setPost($post);

        // Demandons au gestionnaire des entités de réalisé la requête d'insertion en bas.
        $em->persist($postLike);
        $em->flush();

        // Retournons la réponse correspondante au navigateur du client pour qu'il mette à jour les données.
        return $this->json([
            'message' => "Le like a été ajouté.",
            'totalLikes'    => $postLikeRepository->count(['post' => $post])
        ]);
    }
}
