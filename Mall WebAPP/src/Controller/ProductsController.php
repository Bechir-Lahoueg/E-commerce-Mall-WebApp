<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Shop;
use App\Entity\SubCategories;
use App\Repository\ProductsRepository;
use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'all_products')]
    public function indexx(ProductsRepository $productsRepository,ShopRepository $shopRepository): Response
    
    {    
        
       /// $shop= $shopRepository->findAll();  
        $products = $productsRepository->findAll();
        return $this->render('products/indexx.html.twig', [
            'products' => $products,
           // 'shop'=> $shop,
        ]);
        
    }
    #[Route('/{id}', name: 'Products')]
    public function child(SubCategories $subcategories ,Shop $shop ,ProductsRepository $productsRepository, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $products = $productsRepository->findProductsPaginated($page,$subcategories->getId(), $shop->getId());
      
       
        return $this->render('products/index.html.twig', [
            'shop'=> $shop,
            'products' => $products,
            'subcategories'=> $subcategories,
            
            
         
        ]);
    
    }
    

    #[Route('/details/{id}', name: 'details')]
public function details(Products $product, ProductsRepository $productsRepository): Response
{
    // Fetch related products based on the subcategory of the main product
    $relatedProducts = $productsRepository->findBy(['subcategories' => $product->getSubcategories()]);

    return $this->render('products/details.html.twig', [
        
        'product' => $product,
        'relatedProducts' => $relatedProducts,
    ]);
}

//TODO add search product + user + also admin
public function search(Request $request, ProductsRepository $productRepository): JsonResponse
{
    $keyword = $request->query->get('keyword');
    $results = $productRepository->search($keyword);

    // Customize the response based on your needs
    $formattedResults = []; // Convert $results to an array as needed

    return new JsonResponse($formattedResults);
}}
