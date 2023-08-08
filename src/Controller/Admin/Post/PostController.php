<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function create(Request $request, EntityManagerInterface $em) : Response
    {
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
    public function publish(Post $post, EntityManagerInterface $em, Request $request ) : Response
    {

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
    public function show(Post $post) : Response
    {
        return $this->render("pages/admin/post/show.html.twig", compact('post'));
    }

    #[Route('/admin/post/{id}/edit', name: 'admin.post.edit', methods:['GET', 'PUT'])]
    public function edit(Post $post, Request $request, EntityManagerInterface $em) : Response
    {
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
    public function delete(Post $post, Request $request, EntityManagerInterface $em) : Response
    {
        if ( $this->isCsrfTokenValid("post_delete_".$post->getId(), $request->request->get('csrf_token')) ) 
        {
            $em->remove($post);
            $em->flush();

            $this->addFlash("success", "L'article a été supprimé");

        }

        return $this->redirectToRoute('admin.post.index');
    }
}
