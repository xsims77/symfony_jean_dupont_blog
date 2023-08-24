<?php

namespace App\Controller\Admin\Contact;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    #[Route('/admin/contact/list', name: 'admin.contact.index')]
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('pages/admin/contact/index.html.twig', [
            'contacts' => $contactRepository->findAll()
        ]);
    }

    #[Route('/admin/contact/{id<\d+>}/delete', name: 'admin.contact.delete', methods:['DELETE'])]
    public function delete(Contact $contact, Request $request, EntityManagerInterface $em) : Response
    {
        if ($this->isCsrfTokenValid("delete-contact-".$contact->getId(), $request->request->get('csrf_token'))) 
        {
            $em->remove($contact);
            $em->flush();

            $this->addFlash('success', "Le contact a été supprimé");
        }

        return $this->redirectToRoute('admin.contact.index');
    }

    #[Route('/admin/contact/multiple-contacts-delete', name: 'admin.contact.multiple_delete', methods:['DELETE'])]
    public function multipleDelete(
        Request $request, 
        ContactRepository $contactRepository, 
        EntityManagerInterface $em
    ) : Response
    {
        $csrfTokenValue = $request->request->get('csrf_token');


        if ( ! $this->isCsrfTokenValid("multiple_delete_contacts_token_key", $csrfTokenValue) ) 
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
            $contact = $contactRepository->findOneBy(['id' => $id]);


            $em->remove($contact);
            $em->flush();
        }

        return $this->json(
            ['status' => true, "message" => "La suppression multiple a été effectué avec succès."]);
        
        // return new JsonResponse();

    }
}
