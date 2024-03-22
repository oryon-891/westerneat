<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\InfoType;
use App\Utils\CartStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(Order::class)->findByUsers($this->getUser());

        return $this->render('account/index.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/account/orders', name: 'app_sales')]
    public function sales(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(Order::class)->findByUsers($this->getUser());

        return $this->render('account/sales.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/account/update', name: 'app_update_account')]
    public function update_account(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $old_user = $this->getUser();
        $form = $this->createForm(InfoType::class);

        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //$encoder->;
            if($form->get("old_password")->getData() !== null && $form->get("new_password")->getData() !== null){
                $old = $form->get("old_password")->getData();
                if($userPasswordHasher->isPasswordValid($old_user, $old)){
                    $old_user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $old_user,
                            $form->get('new_password')->getData()
                        )
                    );
                }
            }

            $entityManager->persist($old_user);
            $entityManager->flush();

            return $this->redirectToRoute('app_account');
        }
        return $this->render('account/update_info.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/account/update_number', name: 'app_update_number')]
    public function update_number(EntityManagerInterface $entityManager, Request $request)
    {
        $user = $this->getUser();
        $form = $this->createFormBuilder($user)
        ->add('number', TextType::class, [
            'label' => false
        ])    
        ->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $donnee = $form->getData();
            
            try {
                $user->setNumber($donnee->getNumber());
            } catch (\Throwable $th) {  
                //throw $th;
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('account/update_number.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            // 'var' => 'user',
        ]);
    }

    #[Route('/account/orders/{reference}', name: 'app_order_detail')]
    public function show($reference, EntityManagerInterface $entityManager)
    {
        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order || $order->getUsers() != $this->getUser()) {
            return $this->redirectToRoute('account_sales');
        }
        return $this->render('account/order_detail.html.twig', [
            'order' => $order
        ]);
    }
}
