<?php

namespace App\Controller\Admin\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CommentController extends AbstractController
{
    #[Route('/admin/comment/list', name: 'admin.comment.index')]
    public function index(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findAll();

        return $this->render('pages/admin/comment/index.html.twig',[
            "comments"  => $comments
        ]);
    }
    
    #[Route('/admin/comment/{id}/activate', name: 'admin.comment.activate', methods: ['PUT'])]
    public function activate(Comment $comment, EntityManagerInterface $em, Request $request) : Response
    {
        if ( $this->isCsrfTokenValid("comment_activate_".$comment->getId(), $request->request->get('csrf_token')) ) 
        {
            if ( $comment->isIsActivated() == true )
            {
                $comment->setIsActivated(false);
                $this->addFlash("success", "Le commentaire a été désactivé.");
            }
            else
            {
                $comment->setIsActivated(true);
                $this->addFlash("success", "Le commentaire a été activé.");
            }
            
            $em->persist($comment);
            $em->flush();
        }

        return $this->redirectToRoute('admin.comment.index');
    }


    #[Route('/admin/comment/{id}/delete', name: 'admin.comment.delete', methods: ['DELETE'])]
    public function delete(Comment $comment, Request $request, EntityManagerInterface $em) : Response
    {
        if ( $this->isCsrfTokenValid("delete_comment_".$comment->getId(), $request->request->get('csrf_token')) ) 
        {
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', "Le commentaire a été supprimé.");
        }

        return $this->redirectToRoute('admin.comment.index');
    }
}

