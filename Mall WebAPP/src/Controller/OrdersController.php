<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Orders;
use Pagerfanta\Pagerfanta;
use App\Entity\OrdersDetails;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use App\Repository\OrdersDetailsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/orders', name: 'orders_')]
class OrdersController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function info(
        Users $user,
        OrdersDetailsRepository $ordersDetailsRepository,
        Request $request,
        OrdersRepository $ordersRepository
    ): Response {
        // Get the currently authenticated user
        $user = $this->getUser();
         $page = $request->query->getInt('page', 1);
        $maxPerPage = 8;
        // Fetch all orders for the user
        $ordersQuery = $ordersRepository->createQueryBuilder('o')
            ->where('o.users = :user')
            ->setParameter('user', $user)
            ->orderBy('o.created_at', 'DESC'); 

       $adapter = new QueryAdapter($ordersQuery);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        $orders = $pagerfanta->getCurrentPageResults();

        $orderDetails = [];
        foreach ($orders as $order) {
            $orderDetails[] = $ordersDetailsRepository->findBy(['orders' => $order]);
        }

        return $this->render('orders/index.html.twig', [
            'user' => $user,
            'orders' => $orders,
            'orderDetails' => $orderDetails,
            'pagerfanta' => $pagerfanta,
        ]);
    }






    #[Route('/add', name: 'add')]
    public function add(SessionInterface $session,Request $request, ProductsRepository $productsRepository, EntityManagerInterface $em,Users $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $panier= $session->get('panier',[]);
        if($panier === []){
            
            $referer = $request->headers->get('referer') ?: $this->generateUrl('fallback_route');

            return $this->redirect($referer);

        }
        $order = new Orders();
        $order->setUsers($this->getUser());
        $order->setReference('ORDER_' . date('YmdHis') . '_' . uniqid());




        foreach($panier as $item => $quantity){
            $orderDetails = new OrdersDetails();
            $product = $productsRepository->find($item);
            $price= $product->getPrice();

            $orderDetails->setProducts($product);
            $orderDetails->setPrice($price);
            $orderDetails->setQuantity($quantity);

            $order->addOrdersDetail($orderDetails);
         

        };
        $em->persist($order);
        $em->flush();
        $session->remove('panier');
        
        return $this->render('orders/invoice.html.twig', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'user' => $user,
           
            

        ]);
    }
    #[Route('/view/{id}', name: 'view')]
    public function view(Orders $order, OrdersDetailsRepository $ordersDetailsRepository): Response
    {
        // Fetch the order details for the selected order
        $orderDetails = $ordersDetailsRepository->findBy(['orders' => $order]);
       
        // Render the invoice template with the order and orderDetails
        return $this->render('orders/invoice.html.twig', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            
        ]);
    }
}
