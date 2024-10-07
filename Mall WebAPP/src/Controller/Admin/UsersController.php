<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Users;
use App\Form\ProfileUpdateType;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/users', name: 'admin_users_')]
class UsersController extends AbstractController {
    
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {

        $users = $usersRepository->findAll();
        
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
           
        ]);
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
        $form = $this->createForm(ProfileUpdateType::class, $user);

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
        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
           'user' => $user
        ]);
    }









}