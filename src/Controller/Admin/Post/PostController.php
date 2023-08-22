<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostController extends AbstractController
{
    #[Route('/admin/post/list', name: 'admin.post.index')]
    public function index(PostRepository $postRepository): Response
    {

        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('pages/admin/post/index.html.twig',[
            "posts" => $posts
        ]);
    }

    #[Route('/admin/post/create', name: 'admin.post.create', methods:['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository) : Response
    {
        if ( count($categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Vous devez créer au moin une catégorie avant de rédiger des articles.");

            return $this->redirectToRoute('admin.category.index');
        }

        $post = new Post();

        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {

            $post->setUser($this->getUser());

            $em->persist($post);
            $em->flush();

            $this->addFlash("success", "L'article a été créé et sauvegardé.");

            return $this->redirectToRoute('admin.post.index');

        }

        return $this->render("pages/admin/post/create.html.twig", [
            "form" => $form->createView()
        ]);
    }
    #[Route('/admin/post/{id}/publish', name: 'admin.post.publish', methods:['PUT'])]
    public function publish(Post $post, EntityManagerInterface $em, Request $request, CategoryRepository $categoryRepository ) : Response
    {

        if ( count($categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Vous devez créer au moin une catégorie avant de rédiger des articles.");

            return $this->redirectToRoute('admin.category.index');
        }

        if ($this->isCsrfTokenValid('post_publish_'.$post->getId(), $request->request->get('csrf_token'))) 
        {
            // Si l'article a déja été publié,
            if ( $post->isIsPublished() ) 
            {
                // Retirons-là de la liste des publications
                $post->setIsPublished(false);
    
                // Rendons nulle la date de publication
                $post->setPublishedAt(null);
    
                $em->persist($post);
                $em->flush();
    
                // Générons le message flash correspond
                $this->addFlash("success", "Cet article vient d'être retiré de la liste des publications.");
            }
            else
            {
                // Publions l'article
                $post->setIsPublished(true);
    
                // Générons la date de publication
                $post->setPublishedAt(new DateTimeImmutable('now'));
    
                // Générons le message flash correspond
                $this->addFlash("success", "Cet article vient d'être publié.");
            }
    
            // Demandons au gestionnaires des entités de préparer la requête de modification des données
            $em->persist($post);
    
            // Demandons ensuite au gestionnaire (manager) d'exécuter la requête
            $em->flush();
                
        }
        
        // Effectuons une redirection vers l'accueil
        return $this->redirectToRoute('admin.post.index');
    }

    #[Route('/admin/post/{id}/show', name: 'admin.post.show', methods:['GET'])]
    public function show(Post $post, CategoryRepository $categoryRepository) : Response
    {
        if ( count($categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Vous devez créer au moin une catégorie avant de rédiger des articles.");

            return $this->redirectToRoute('admin.category.index');
        }
        return $this->render("pages/admin/post/show.html.twig", compact('post'));
    }

    #[Route('/admin/post/{id}/edit', name: 'admin.post.edit', methods:['GET', 'PUT'])]
    public function edit(Post $post, Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository) : Response
    {

        if ( count($categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Vous devez créer au moin une catégorie avant de rédiger des articles.");

            return $this->redirectToRoute('admin.category.index');
        }

        $form = $this->createForm(PostFormType::class, $post, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $post->setUser($this->getUser());

            $em->persist($post);

            $em->flush();

            $this->addFlash("success", "L'article a été modifier et sauvegardé.");

            return $this->redirectToRoute("admin.post.index");
        }

        return $this->render("pages/admin/post/edit.html.twig", [
            "form" => $form->createView(),
            "post" => $post
        ]);
    }

    #[Route('/admin/post/{id}/delete', name: 'admin.post.delete', methods:['DELETE'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository) : Response
    {

        if ( count($categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Vous devez créer au moin une catégorie avant de rédiger des articles.");

            return $this->redirectToRoute('admin.category.index');
        }

        if ( $this->isCsrfTokenValid("delete_post_" . $post->getId(), $request->request->get('csrf_token'))) 
        {
            $em->remove($post);
            $em->flush();
            
            $this->addFlash("success", "L'article a été supprimé");
        }

        return $this->redirectToRoute('admin.post.index');
    }

    #[Route('/admin/post/multiple-posts-delete', name: 'admin.post.multiple_delete', methods:['DELETE'])]
    public function multipleDelete(
        Request $request, 
        PostRepository $postRepository, 
        EntityManagerInterface $em
    ) : Response
    {
        $csrfTokenValue = $request->request->get('csrf_token');


        if ( ! $this->isCsrfTokenValid("multiple_delete_posts_token_key", $csrfTokenValue) ) 
        {
            return $this->json(
                ['status' => false, "message" => "Un problème est suvenu, veuillez réessayer." ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $ids = $request->request->get('ids');

        $ids = explode(",", $ids);

        foreach ($ids as $id) 
        {
            $post = $postRepository->findOneBy(['id' => $id]);


            $em->remove($post);
            $em->flush();
        }

        return $this->json(
            ['status' => true, "message" => "La suppression multiple a été effectué avec succès."]);
        
        // return new JsonResponse();

    }
}
