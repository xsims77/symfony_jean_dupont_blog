<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'visitor.contact.create')]
    public function create(): Response
    {
        $contact = new Contact();

        
        return $this->render('pages/visitor/contact/create.html.twig');
    }
}
