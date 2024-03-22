<?php

namespace App\Controller;

use App\Utils\CartStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    
    public function index($reference, EntityManagerInterface $entityManager, CartStorage $cart)
    {

        /*$payementStripes = $entityManager->getRepository(Sycommerce::class)->findAll();

        foreach ($payementStripes as $payementStripe) {
            $domaine   = $payementStripe->getDomaine();
            $cleprive = $payementStripe->getClepriver();
            $clepublic = $payementStripe->getClepublique();
        }
        $products_for_stripe = [];
        // $YOUR_DOMAIN = 'https://grcc.fr';
        $YOUR_DOMAIN = $domaine;

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order || $order == null) {
            $response = new JsonResponse(['error' => 'order']);
        } else {

            foreach ($order->getOrderDetails()->getValues() as $product) {
                $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
                $products_for_stripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => round($product->getPrice() * 100),
                        'product_data' => [
                            'name' => $product->getProduct(),
                            // 'images' => [$YOUR_DOMAIN."/uploads/".$product_object->getIllustration()],
                        ],
                    ],
                    'quantity' => $product->getQuantity(),
                ];
            }

            $commission = ($product->getPrice() * $product->getQuantity() * $order->getCarrierPrice())  / 10000;
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    // 'unit_amount' => $order->getCarrierPrice(),
                    'unit_amount' => round($commission * 100),
                    'product_data' => [
                        'name' => $order->getCarrierName(),
                        'images' => [$YOUR_DOMAIN],
                    ],
                ],
                'quantity' => 1,
            ];

            //Stripe::setApiKey('');

            Stripe::setApiKey($cleprive);

            $checkout_session = Session::create([
                'customer_email' => $this->getUser()->getEmail(),
                'payment_method_types' => ['card'],
                'line_items' => [
                    $products_for_stripe
                ],
                'mode' => 'payment',
                'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
            ]);

            $order->setStripeSessionId($checkout_session->id);
            $entityManager->flush();

            $response = new JsonResponse(['id' => $checkout_session->id]);
        }

        return $response;*/
        return $this->render('product/index.html.twig');
    }
}
