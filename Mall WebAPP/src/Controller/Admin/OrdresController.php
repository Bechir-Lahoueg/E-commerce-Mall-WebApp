<?php

namespace App\Controller\Admin;

use App\Entity\Linker;
use App\Entity\Orders;
use App\Enum\Grade;
use App\Repository\LinkerRepository;
use App\Repository\OrdersDetailsRepository;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/orders', name: 'admin_orders_')]
class OrdresController extends AbstractController {

    #[Route('/', name: 'index')]
    public function index(Request $request, OrdersRepository $ordersRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxPerPage = 8;

        $ordersQuery = $ordersRepository->createQueryBuilder('o')
        ->orderBy('o.created_at', 'DESC'); 

        // Use the correct namespace for the Doctrine ORM adapter
        $adapter = new QueryAdapter($ordersQuery);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        $orders = $pagerfanta->getCurrentPageResults();

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $orders,
            'pagerfanta' => $pagerfanta,
        ]);
    }
    #[Route('/view/{id}', name: 'view')]
    public function view(Orders $order, OrdersDetailsRepository $ordersDetailsRepository): Response
    {
        // Fetch the order details for the selected order
        $orderDetails = $ordersDetailsRepository->findBy(['orders' => $order]);
       
        // Render the invoice template with the order and orderDetails
        return $this->render('admin/orders/invoice.html.twig', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            
        ]);
    }
    private function calculateTeamConsumption($allGenerations, $ordersRepository): float
    {
        $teamConsumption = 0;

        foreach ($allGenerations as $generation) {
            foreach ($generation as $teamMember) {
                // Fetch orders for the current team member
                $orders = $ordersRepository->findBy(['users' => $teamMember]);

                foreach ($orders as $order) {
                    if ($order->isIsConfirmed()) {
                        foreach ($order->getOrdersDetails() as $orderDetail) {

                            $teamConsumption += $orderDetail->getTotal();
                        }
                    }
                }
            }
        }

        return $teamConsumption;
    }
    private function calculateTotalConsumption($orders): float
    {
        $totalConsumptionn = 0;

        foreach ($orders as $order) {
            // Check if the order is confirmed before considering it for consumption
            if ($order->isIsConfirmed()) {
                foreach ($order->getOrdersDetails() as $orderDetail) {
                    $totalConsumptionn += $orderDetail->getTotal();
                }
            }
        }

        return $totalConsumptionn;
    }
    private function calculateTeamMonthlyConsumption($allGenerations, $ordersRepository): float
    {
        $teamMonthlyConsumption = 0;

        foreach ($allGenerations as $generation) {
            foreach ($generation as $teamMember) {
                // Fetch monthly orders for the current team member
                $monthlyOrders = $ordersRepository->findMonthlyOrders($teamMember);

                foreach ($monthlyOrders as $order) {
                    if ($order->isIsConfirmed()) {
                        foreach ($order->getOrdersDetails() as $orderDetail) {
                            $teamMonthlyConsumption += $orderDetail->getTotal();
                        }
                    }
                }
            }
        }

        return $teamMonthlyConsumption;
    }

    #[Route('confirm/{id}', name: 'confirm')]
    public function confirm(Orders $order, EntityManagerInterface $em, LinkerRepository $linkerRepository, UsersRepository $usersRepository, OrdersRepository $ordersRepository, ): Response
    {

        $user = $usersRepository->find($order->getUsers()->getId());


        if (!$user) {
            // Handle the case where the user does not exist
            // ...
    
            return $this->redirectToRoute('admin_orders_index'); // or any other appropriate action
        }


        $linker = $linkerRepository->findOneBy(['user' => $user]);


        if (!$linker) {

            $order->setIsConfirmed(true);

        }else{


            $parentUser = $linker->getUser()->getParent();
        $orderCommission = $linker->getOrderCommission() ?? 0;
        //TODO case  4 grades ++
        $orderCommission += (0.15 * $order->getTotalAmount());
    
        $linker->setOrderCommission($orderCommission);
        
        $linker->setTotalConsumption($linker->getTotalConsumption() + $order->getTotalAmount());
            // $linker->setMonthlyConsumption($linker->getTotalConsumption() );

        $sold = $orderCommission;

            if ($parentUser) {

                $parentCommission = $parentUser->getLinker()->getParentComission() ?? 0;
                // Fix  $parentCommission
            $parentCommission += (0.15 * $order->getTotalAmount());
            $parentUser->getLinker()->setParentComission($parentCommission);
            $parentUser->getLinker()->setSolde($parentCommission+ $parentUser->getLinker()->getOrderCommission());

            $em->persist($parentUser);


        }
        $linker->setSolde($sold + $linker->getParentComission());

        $em->persist($linker);

            $order->setIsConfirmed(true);
    }

        $em->persist($order);
        $em->flush();
        $linkers = $linkerRepository->findAll();
        foreach ($linkers as $linker) {
            $monthlyOrders = $ordersRepository->findMonthlyOrders($linker->getUser());
            $monthlyConsumption = $this->calculateTotalConsumption($monthlyOrders);

            if ($monthlyConsumption >= $this->threshold($linker)) {

                $linker->setIsActive(true);
            } else {
                $linker->setIsActive(false);
            }
            $allGenerations = $usersRepository->getAllGenerations($linker->getUser());
            $teamConsumption = $this->calculateTeamConsumption($allGenerations, $ordersRepository);
            $teamMonthlyConsumption = $this->calculateTeamMonthlyConsumption($allGenerations, $ordersRepository);
            $linker->setMonthlyConsumption($monthlyConsumption);
            $linker->setTeamMonthlyConsumption($teamMonthlyConsumption);
            $linker->setTeamConsumption($teamConsumption);
            $em->persist($linker);
            $em->flush();
        }

        $this->addFlash('success', 'Order confirmed with success');
        return $this->redirectToRoute('admin_orders_index');
    }
    private function threshold(Linker $linker): float
    {
        $grade = $linker->getGrade();
        if ($grade) {
            switch ($grade) {
                case Grade::LINKER:
                    return 50.0;
                case Grade::ASSOCIATER:
                    return 70.0;
                case Grade::CONNECTER:
                    return 90.0;
                case Grade::BUILDER:
                    return 110.0;
                case Grade::NETWORKER:
                    return 130.0;
                case Grade::LEADER:
                    return 150.0;
                case Grade::SUPER_LEADER:
                    return 170.0;
                case Grade::MANAGER:
                    return 190.0;
                case Grade::DIRECTOR:
                    return 210.0;
                case Grade::DIAMOND:
                    return 230.0;
                case Grade::ELITE:
                    return 250.0;
                case Grade::AMBASSADOR:
                    return 300.0;
                case Grade::PRESIDENTIAL:
                    return 0.0;
                default:
                    return 0.0;
            }
        } else {
            return 0.0;
        }
    }

//TODO challenge


}