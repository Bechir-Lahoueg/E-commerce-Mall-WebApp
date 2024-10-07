<?php

namespace App\Controller\Admin;



use App\Entity\SubCategories;
use App\Form\SubCategoriesFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubCategoriesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/admin/subcategories', name: 'admin_subcategories_')]
class SubCategoriesController extends AbstractController {
    
    #[Route('/', name: 'index')]
    public function index(SubCategoriesRepository $subcategoriesRepository): Response
    {

        $subcategories = $subcategoriesRepository->findAll();
        
        return $this->render('admin/subcategories/index.html.twig', [
            'subcategories' => $subcategories,
           
        ]);
    }
    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, #[Autowire('%subcategories_directory%')] string $photoDir, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $subcategories = new SubCategories(); // Note: Use "new subcategories()" to create a new subcategories entity
    
        $subcategoriesForm = $this->createForm(SubCategoriesFormType::class, $subcategories);
    
        $subcategoriesForm->handleRequest($request);
    
        if ($subcategoriesForm->isSubmitted() && $subcategoriesForm->isValid()) {
            // Get the uploaded photo from the form
            $photo = $subcategoriesForm['photo']->getData();
    
            // Check if a new photo was uploaded
            if ($photo) {
                // Generate a unique filename and move the uploaded file
                $fileName = uniqid().'.'.$photo->guessExtension();
                $photo->move($photoDir, $fileName);
    
                // Set the new image filename on the subcategories entity
                $subcategories->setImageFileNameSubCat($fileName);
    
                $this->addFlash('success', 'subcategories picture updated successfully.');
            } else {
                $this->addFlash('danger', 'subcategories picture is not set.');
            }
    
            // Persist and flush the entity
            $em->persist($subcategories);
            $em->flush();
    
            $this->addFlash('success', 'Product added with success');
    
            return $this->redirectToRoute('admin_subcategories_index');
        }
    
        return $this->render('admin/subcategories/add.html.twig', [
            'subcategories' => $subcategories,
            'subcategoriesForm' => $subcategoriesForm->createView(),
        ]);
    }


    #[Route('/edit/{id}', name: 'edit')]
    public function edit(SubCategories $subcategories,Request $request, EntityManagerInterface $em, #[Autowire('%subcategories_directory%')] string $photoDir, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
       
        $subcategoriesForm = $this->createForm(SubCategoriesFormType::class, $subcategories);
    
        $subcategoriesForm->handleRequest($request);
    
        if ($subcategoriesForm->isSubmitted() && $subcategoriesForm->isValid()) {
            // Get the uploaded photo from the form
            $photo = $subcategoriesForm['photo']->getData();
    
            // Check if a new photo was uploaded
            if ($photo) {
                // Generate a unique filename and move the uploaded file
                $fileName = uniqid().'.'.$photo->guessExtension();
                $photo->move($photoDir, $fileName);
    
                // Set the new image filename on the subcategories entity
                $subcategories->setImageFileNameSubCat($fileName);
    
                $this->addFlash('success', 'subcategories picture updated successfully.');
            } else {
                $this->addFlash('danger', 'subcategories picture is not set.');
            }
    
            // Persist and flush the entity
            $em->persist($subcategories);
            $em->flush();
    
            $this->addFlash('success', 'subcategories added with success');
    
            return $this->redirectToRoute('admin_subcategories_index');
        }
    
        return $this->render('admin/subcategories/add.html.twig', [
            'subcategories' => $subcategories,
            'subcategoriesForm' => $subcategoriesForm->createView(),
        ]);}






}