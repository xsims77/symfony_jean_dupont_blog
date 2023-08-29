<?php

namespace App\Controller\Admin\Profile;

use App\Form\EditProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\EditProfilePasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/admin/profile', name: 'admin.profile.index')]
    public function index(): Response
    {
        return $this->render('pages/admin/profile/index.html.twig');
    }

    #[Route('/admin/profile/edit', name: 'admin.profile.edit', methods:['GET', 'PUT'])]
    public function edit(Request $request, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditProfileFormType ::class, $user, [
            "method"    => "PUT"
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "Votre profil a bien été modifié.");

            return $this->redirectToRoute('admin.profile.index');
        }

        return $this->render("pages/admin/profile/edit.html.twig", [
            "form"  => $form->createView()
        ]);
    }

    #[Route('/admin/profile/edit_password', name: 'admin.profile.edit_password', methods:['GET', 'PUT'])]
    public function editPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditProfilePasswordFormType::class, null, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $data = $request->request->all();
            $password = $data['edit_profile_password_form']['password']['first'];
            $passwordHashed = $hasher->hashPassword($user, $password);
            $user->setPassword($passwordHashed);

            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "Votre nouveau mot de passe a bien été pris en compte");

            return $this->redirectToRoute('admin.profile.index');
        }

        return $this->render("pages/admin/profile/edit_password.html.twig", [
            'form'  => $form->createView()
        ]);
    }
}
