<?php

namespace App\Controller\Admin\Category;

use App\Entity\Category;
use App\Form\CategoryFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/admin/category/list', name: 'admin.category.index')]
    public function index(): Response
    {
        return $this->render('pages/admin/category/index.html.twig');
    }

    #[Route('/admin/category/create', name: 'admin.category.create', methods:['GET', 'POST'])]
    public function create(Request $request) : Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            dd('ok');
        }

        return $this->render("pages/admin/category/create.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
