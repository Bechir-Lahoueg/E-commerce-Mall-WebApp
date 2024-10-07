<?php

namespace App\Controller;

use App\Enum\Grade;
use App\Entity\Linker;
use App\Entity\Users;
use App\Form\LinkerUpdateType;
use App\Form\ProfilePictureType;
use App\Form\ProfileUpdateType;
use App\Repository\LinkerRepository;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Users as EntityUsers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'user')]
    public function index(UsersRepository $userRepository,
       OrdersRepository $ordersRepository ,
      EntityManagerInterface $entityManager,
      LinkerRepository $linkerRepository): Response
    {
        $user = $this->getUser();
        $linker = $linkerRepository->findOneBy(['user' => $user]);
        $currentDate = new \DateTime();

        // Format the date as per your requirement
        $formattedDate = $currentDate->format('F d , Y');
       
        // Get all generations for the user
        $allGenerations = $userRepository->getAllGenerations($user);

        // Calculate the number of team members
        $teamMembersCount = array_sum(array_map('count', $allGenerations));
        $activeTeamMembersCount = array_sum(array_map('count', array_filter($allGenerations, function($generation) {
            // Filter out inactive linkers
            return array_filter($generation, function($teamMember) {
                $linker = $teamMember->getLinker();
                // Check if the linker is not null and is active
                return $linker !== null && $linker->isIsActive();
            });
        })));
        $generationCounts = array_map('count', $allGenerations);

        // Fetch orders for the current user
        $orders = $user->getOrders();
       
        if ($linker){
         $totalConsumption =$linker->getTotalConsumption();
         $parentCommission= $linker->getParentComission();
         $orderCommission= $linker->getOrderCommission();
         $solde= $linker->getSolde();
         $earning = $linker->getEarning();

        $teamConsumption = $linker->getTeamConsumption();
        $monthlyConsumption = $linker->getMonthlyConsumption();
//
        $entityManager->flush();
        $objectif=$this->threshold($linker);
        // Calculate the difference to be active
        $missingToBeActive = max(0, $objectif - $monthlyConsumption);
        $referralCode = $user->getReferralCode();
        $registrationLink = $this->generateUrl('app_register_linker', ['referralCode' => $referralCode], true);
        $monthlyTeamConsumption = $linker->getTeamMonthlyConsumption();
        $currentGrade = $linker->getGrade();
        $nextGrade = $this->getNextGrade($currentGrade);

        }
        else{
            $totalConsumption=0;
            $teamConsumption=0;
            $monthlyConsumption=0;
            $parentCommission=0;
            $missingToBeActive=0;
            $referralCode=0;
            $registrationLink=0;
            $orderCommission=0;
            $solde=0;
            $earning=0;
            $currentGrade=0;
            $monthlyTeamConsumption=0;
            $nextGrade=0;
            $objectif=0;
        }
       
       
       
        return $this->render('profile/index.html.twig', [
            'teamMembersCount' => $teamMembersCount,
            'activeTeamMembersCount' => $activeTeamMembersCount,
            'generationCounts' => $generationCounts,
            'totalConsumption' => $totalConsumption,
            'teamConsumption' => $teamConsumption,
            'monthlyConsumption' => $monthlyConsumption,
            'orders' => $orders,
            'parentCommission' => $parentCommission,
            'missingToBeActive' => $missingToBeActive,
            'referralCode' => $referralCode,
            'registrationLink' => $registrationLink,
            'orderCommission' => $orderCommission,
            'solde'=>  $solde,
            'earning' =>$earning,
            'currentDate' => $formattedDate,
            'currentGrade' => $currentGrade,
            'nextGrade' => $nextGrade,
            'allGenerations' => $allGenerations,
            'monthlyTeamConsumption'=>$monthlyTeamConsumption,
            'objectif'=>$objectif,


        ]);
    }



    #[Route('/user/{id}', name: 'user_viste')]
    public function updateUservist($id, UsersRepository $userRepository, LinkerRepository $linkerRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $linker = $linkerRepository->findOneBy(['user' => $user]);
        $allGenerations = $userRepository->getAllGenerations($user);

        // Calculate the number of team members
        $teamMembersCount = count($allGenerations);
        $curentGrade= $linker->getGrade();

        // Calculate the number of members in each generation
        $generationCounts = array_map('count', $allGenerations);

        return $this->render('profile/user.html.twig', [
            'linker' => $linker,
            'allGenerations' => $allGenerations,
            'teamMembersCount' => $teamMembersCount,
            'generationCounts' => $generationCounts,
            'curentGrade' =>$curentGrade,
            'user'=>$user
        ]);
    }





    #[Route('/update-picture', name: 'update_picture')]
    public function updateProfilePicture(
        Request $request,
        EntityManagerInterface $entityManager,
        #[Autowire('%image_directory%')] string $photoDir
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfilePictureType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form['photo']->getData();
            if ($photo) {
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $photo->move($photoDir, $fileName);
                $user->setImageFileName($fileName);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Profile picture updated successfully.');
            } else {
                $this->addFlash('danger', 'Profile picture is not set.');
            }
        }

        return $this->render('profile/update_picture.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update-profile', name: 'update_profile')]
    public function updateProfile(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfileUpdateType::class, $user);
       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPlainPassword = $request->request->get('plainPassword');

            if ($newPlainPassword) {
                // Hash the new plain password
                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPlainPassword);
    
                // Set the hashed password on the user entity
                $user->setPassword($hashedPassword);
            }
    
            $entityManager->persist($user);
            $entityManager->flush();
        }
      

        return $this->render('profile/update_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/update-Linker', name: 'update_Linker')]
    public function updateLinker(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(LinkerUpdateType::class, $user);
       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPlainPassword = $request->request->get('plainPassword');

            if ($newPlainPassword) {
                // Hash the new plain password
                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPlainPassword);
    
                // Set the hashed password on the user entity
                $user->setPassword($hashedPassword);
            }
    
            $entityManager->persist($user);
            $entityManager->flush();
        }
      

        return $this->render('profile/update_Linker.html.twig', [
            'form' => $form->createView(),
        ]);
    }
   
    #[Route('/link/{id}', name: 'link')]
    public function link(int $id, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the user from the database based on the provided ID
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // You can access the user's referral code using $user->getReferralCode()
        $referralCode = $user->getReferralCode();

        $registrationLink = $this->generateUrl('app_register_linker', ['referralCode' => $referralCode], true);

        return $this->render('profile/link.html.twig', [
            'user' => $user,
            'referralCode' => $referralCode,
            'registrationLink' => $registrationLink,
        ]);
    }
    private function getNextGrade($currentGrade)
    {
        $grades = [
            Grade::LINKER,
            Grade::ASSOCIATER,
            Grade::CONNECTER,
            Grade::BUILDER,
            Grade::NETWORKER,
            Grade::LEADER,
            Grade::SUPER_LEADER,
            Grade::MANAGER,
            Grade::DIRECTOR,
            Grade::DIAMOND,
            Grade::ELITE,
            Grade::AMBASSADOR,
            Grade::PRESIDENTIAL,
        ];

        $currentGradeIndex = array_search($currentGrade, $grades);

        if ($currentGradeIndex !== false && isset($grades[$currentGradeIndex + 1])) {
            return $grades[$currentGradeIndex + 1];
        }

        return $currentGrade;
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
  


}
