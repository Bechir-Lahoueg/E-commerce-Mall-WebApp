<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Enum\Grade;
use App\Entity\Linker;
use App\Form\LinkerUpdateType;
use App\Repository\LinkerRepository;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Webmozart\Assert\Tests\StaticAnalysis\string;


#[Route('/admin/linker', name: 'admin_linker_')]
class LinkerController extends AbstractController {
    
    #[Route('/', name: 'index')]
    public function index(LinkerRepository $linkerRepository): Response
    {

        $linker = $linkerRepository->findAll();
        
        return $this->render('admin/Linker/index.html.twig', [
            'linker' => $linker,
           
        ]);
    }


//    private function calculateTeamConsumption($allGenerations, $ordersRepository): float
//    {
//        $teamConsumption = 0;
//
//        foreach ($allGenerations as $generation) {
//            foreach ($generation as $teamMember) {
//                // Fetch orders for the current team member
//                $orders = $ordersRepository->findBy(['users' => $teamMember]);
//
//                foreach ($orders as $order) {
//                    if ($order->isIsConfirmed()) {
//                        foreach ($order->getOrdersDetails() as $orderDetail) {
//
//                            $teamConsumption += $orderDetail->getTotal();
//                        }
//                    }
//                }
//            }
//        }
//
//        return $teamConsumption;
//    }


//    private function calculateTotalConsumption($orders): float
//    {
//        $totalConsumptionn = 0;
//
//        foreach ($orders as $order) {
//            // Check if the order is confirmed before considering it for consumption
//            if ($order->isIsConfirmed()) {
//                foreach ($order->getOrdersDetails() as $orderDetail) {
//                    $totalConsumptionn += $orderDetail->getTotal();
//                }
//            }
//        }
//
//        return $totalConsumptionn;
//    }




//    #[Route('/update/data', name: 'update')]
//    public function update(LinkerRepository $linkerRepository, OrdersRepository $ordersRepository, UsersRepository $usersRepository, EntityManagerInterface $em): Response
//    {
//        $linkers = $linkerRepository->findAll();
//        foreach ($linkers as $linker) {
//            $monthlyOrders = $ordersRepository->findMonthlyOrders($linker->getUser());
//            $monthlyConsumption = $this->calculateTotalConsumption($monthlyOrders);
//
//            if ($monthlyConsumption >= $this->threshold($linker)) {
//                $linker->setIsActive(true);
//            } else {
//                $linker->setIsActive(false);
//            }
//            $allGenerations = $usersRepository->getAllGenerations($linker->getUser());
//            $teamConsumption = $this->calculateTeamConsumption($allGenerations, $ordersRepository);
//            $linker->setMonthlyConsumption($monthlyConsumption);
//            $linker->setTeamConsumption($teamConsumption);
//            $em->persist($linker);
//            $em->flush();
//        }
//
//        return $this->render('admin/Linker/index.html.twig', [
//            'linker' => $linkers,
//        ]);
//    }
    private function passageGrade(Linker $linker): string
    {
        $totalConsumption = $linker->getTotalConsumption();
        $teamConsumption= $linker->getTeamConsumption();
        $statut= $linker->isIsActive();
        $grades = $linker->getGrade();

        // Use a switch statement to check different conditions for each grade
        switch (true) {
            case ($totalConsumption >= 10000000000000000000000000000000.0 ):
                return Grade::PRESIDENTIAL;

            case ($totalConsumption >= 230.0):
                return Grade::DIAMOND;
            case ($totalConsumption >= 4500.0):
                return Grade::DIRECTOR;
            // Add more cases for other grades...

            // Default case if no conditions are met
            default:
                return $grades;
        }
    }
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(
        Users $user,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Ensure the current user has the ROLE_ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Create a form using the ProfileUpdateType form type and the provided user entity
        $form = $this->createForm(LinkerUpdateType::class, $user);

        // Handle the form submission
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the new plain password from the request
            $newPlainPassword = $request->request->get('plainPassword');

            if ($newPlainPassword) {
                // Hash the new plain password using the provided userPasswordHasher service
                $hashedPassword = $userPasswordHasher->hashPassword($user, $newPlainPassword);

                // Set the hashed password on the user entity
                $user->setPassword($hashedPassword);
            }

            // Persist the user entity to the database
            $entityManager->persist($user);
            $entityManager->flush();
        }

        // Render the profile update template with the form
        return $this->render('admin/Linker/edit.html.twig', [
            'form' => $form->createView(),
            'Linker' => $user
        ]);
    }
//    #[Route('/update/data/grade', name: 'update_grade')]
//    public function updateGrade(LinkerRepository $linkerRepository, OrdersRepository $ordersRepository, UsersRepository $usersRepository, EntityManagerInterface $em): Response
//    {
//        $linkers = $linkerRepository->findAll();
//        foreach ($linkers as $linker) {
//
//
//
//        }
//
//        return $this->render('admin/Linker/index.html.twig', [
//            'linker' => $linkers,
//        ]);
//    }
    #[Route('/paid/{id}/{solde}', name: 'paid')]
    public function paid(LinkerRepository $linkerRepository, $id, $solde, EntityManagerInterface $em): Response
    {
        $linker = $linkerRepository->find($id);
        $currentGrade= $linker->getGrade();
    
        // Check if the linker exists
        if (!$linker) {
            throw new NotFoundHttpException('Linker not found.');
        }
    
        // Check if the linker is already paid
        if ($linker->getSolde()==0 ) {
             $this->addFlash('danger', 'Linker already paid.');
            
        }
        else{

          if ($linker->isIsActive()=== true) {
            // Update linker details
            $linker->setEarning($linker->getEarning() + $solde);
            $linker->setIsPaid(true);
            $linker->setSolde(0);
            $linker->setOrderCommission(0);
            $linker->setParentComission(0);
            $linker->setMonthlyConsumption(0);
            $linker->setTeamMonthlyConsumption(0);
            $grade = $this->passageGrade($linker);
            $linker->setGrade($grade);
             }
          else{
              $linker->setSolde(0);
              $linker->setOrderCommission(0);
              $linker->setParentComission(0);
              $linker->setTotalConsumption( $linker->getTotalConsumption() - $linker->getMonthlyConsumption());
              $linker->setTeamConsumption( $linker->getTeamConsumption() - $linker->getTeamMonthlyConsumption());
              $linker->setMonthlyConsumption(0);
              $linker->setTeamMonthlyConsumption(0);
              $linker->setGrade($currentGrade);


          }

        $em->persist($linker);
        $em->flush();
    }
        $linker = $linkerRepository->findAll();
        return $this->render('admin/Linker/index.html.twig', [
            'linker' => $linker,
        ]);
        
    }

 






}