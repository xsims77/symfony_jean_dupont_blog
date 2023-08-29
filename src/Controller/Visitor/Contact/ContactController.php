<?php

namespace App\Controller\Visitor\Contact;


use App\Entity\Contact;
use App\Form\ContactFormType;
use App\Repository\SettingRepository;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'visitor.contact.create' , methods:['GET','POST'])]
    public function create(Request $request, EntityManagerInterface $em, SendEmailService $sendEmailService, SettingRepository $settingRepository): Response
    {
        $contact = new Contact();

        $data = $settingRepository->findAll();

        $setting = $data[0];

        $form = $this->createForm(ContactFormType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Envoie d'un email
            $sendEmailService->send([
                "sender_email"      => "medecine-du-monde@gmail.com",
                "sender_name"       => "Jean Dupont",
                "recipient_email"   => "medecine-du-monde@gmail.com",
                "subject"           => "Un message reçu concernant votre blog",
                "html_template"     => "email/contact.html.twig",
                "context"           => [
                    "contact_first_name"      => $contact->getFirstName(),
                    "contact_last_name"      => $contact->getLastName(),
                    "contact_email"     => $contact->getEmail(),
                    "contact_phone"     => $contact->getPhone(),
                    "contact_message"   => $contact->getMessage(),
                ]
            ]);

            $em->persist($contact);
            $em->flush();

            $this->addFlash("success", "Votre message a bien été envoyé. Je vous répondrais dans les plus bref délais.");
            return $this->redirectToRoute('visitor.contact.create');
        }

        return $this->render('pages/visitor/contact/create.html.twig', [
            "form"      => $form->createView(),
            "setting"   => $setting
        ]);
    }
}
