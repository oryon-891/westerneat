<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/signin', name: 'app_signin')]
    public function signin(EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
         $error = $authenticationUtils->getLastAuthenticationError();

         // last username entered by the user
         $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/signin.html.twig',[
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/signup', name: 'app_signup')]
    public function signup(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, CityRepository $cityRepository): Response
    {
        $listing = [];
        foreach ($cityRepository->findAll() as $q) {
            //array_push($listing, join(", ", array($q["name"], $q["quarter"])));
            array_push($listing, array($q->getId(), $q->getName()));
        }
        $form = $this->createForm(UserType::class);

        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setLocation(join(", ", array($request->request->get('zone'), $request->request->get('district'))));
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('auth/signup.html.twig', [
            'form' => $form,
            'listings' => $listing
        ]);
    }
}
