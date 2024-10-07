<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Entity\SubCategories;
use App\Form\CategoriesFormType;
use App\Form\SubCategoriesFormType;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

use Pagerfanta\Doctrine\ORM\QueryAdapter;

use App\Repository\CategoriesRepository;


use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin/categories', name: 'admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxPerPage = 10; // Change the value as needed

        $categoriesQuery = $categoriesRepository->createQueryBuilder('c')
            ->getQuery();

            $adapter = new QueryAdapter($categoriesQuery,false);

            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage($maxPerPage);
            $pagerfanta->setCurrentPage($page);
        $categories= $pagerfanta->getCurrentPageResults();

        return $this->render('admin/categories/index.html.twig', [
            'pagerfanta' => $pagerfanta,
            'categories' => $categories,
        ]);
    }


#[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, #[Autowire('%subcategories_directory%')] string $photoDir, SluggerInterface $slugger): Response
{
    $this->denyAccessUnlessGranted('ROLE_PRODUCT_ADMIN');
    $categories = new Categories(); // Note: Use "new subcategories()" to create a new subcategories entity

    $categoriesForm = $this->createForm(CategoriesFormType::class, $categories);

    $categoriesForm->handleRequest($request);

    if ($categoriesForm->isSubmitted() && $categoriesForm->isValid()) {
        // Get the uploaded photo from the form
        $photo = $categoriesForm['photo']->getData();

        // Check if a new photo was uploaded
        if ($photo) {
            // Generate a unique filename and move the uploaded file
            $fileName = uniqid() . '.' . $photo->guessExtension();
            $photo->move($photoDir, $fileName);

            // Set the new image filename on the subcategories entity
            $categories->setImageFileNameCat($fileName);

            $this->addFlash('success', 'Categories picture updated successfully.');
        } else {
            $this->addFlash('danger', 'Categories picture is not set.');
        }

        // Persist and flush the entity
        $em->persist($categories);
        $em->flush();

        $this->addFlash('success', 'Categories added with success');

        return $this->redirectToRoute('admin_categories_index');
    }

    return $this->render('admin/categories/add.html.twig', [
        'categories' => $categories,
        'categoriesForm' => $categoriesForm->createView(),
    ]);

}
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Categories $categories, Request $request, EntityManagerInterface $em, #[Autowire('%subcategories_directory%')] string $photoDir, SluggerInterface $slugger): Response
{
    $this->denyAccessUnlessGranted('ROLE_PRODUCT_ADMIN');

    $categoriesForm = $this->createForm(CategoriesFormType::class, $categories);

    $categoriesForm->handleRequest($request);

    if ($categoriesForm->isSubmitted() && $categoriesForm->isValid()) {
        // Get the uploaded photo from the form
        $photo = $categoriesForm['photo']->getData();

        // Check if a new photo was uploaded
        if ($photo) {
            // Generate a unique filename and move the uploaded file
            $fileName = uniqid() . '.' . $photo->guessExtension();
            $photo->move($photoDir, $fileName);

            // Set the new image filename on the subcategories entity
            $categories->setImageFileNameCat($fileName);

            $this->addFlash('success', 'Categories picture updated successfully.');
        } else {
            $this->addFlash('danger', 'Categories picture is not set.');
        }

        // Persist and flush the entity
        $em->persist($categories);
        $em->flush();

        $this->addFlash('success', 'Categories added with success');

        return $this->redirectToRoute('admin_categories_index');
    }

    return $this->render('admin/categories/add.html.twig', [
        'categories' => $categories,
        'categoriesForm' => $categoriesForm->createView(),
    ]);
}



 }






