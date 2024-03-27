<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Utils\CartStorage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;
use App\Entity\Order;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(CartStorage $cart): Response
    {
        $ref = $cart->getExtra();
        return $this->render('order/index.html.twig', [
            'items' => $cart->getItems(),
            'cart' => $cart->getItems(),
            'ref' => $ref,
        ]);
    }

    #[Route('/order_send/stripe/{reference}', name: 'app_order_stripe_send')]
    public function send_stripe(OrderRepository $repository, $reference, EntityManagerInterface $em): Response
    {
        $order = $repository->findOneByReference($reference);
        if (!$order) {
            throw $this->createNotFoundException('Cette commande n\'existe pas');
        }
        $products = $order->getOrderDetails()->getValues();
        $productsForStripe = [];
        foreach ($products as $item) {
            $productsForStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $item->getPrice(),
                    'product_data' => [
                        'name' => $item->getName()
                    ]
                ],
                'quantity' => $item->getQuantity()
            ];
        }
        // Ajout des frais de livraison
        $productsForStripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => 5,
                'product_data' => [
                    'name' => $order->getCarrierName()
                ]
            ],
            'quantity' => 1
        ];
        Stripe::setApiKey('sk_test_51M9qP1Ek3EJ3XoccudSM2zIGb0g1wqzJkMkKEnGWEpLAifgT2Ze2BQv2yMsMEAPTrkQOQhCT99pA5ZD6Nu2vkzmK00j7fspqE6');
        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'https://westerneat.osc-fr1.scalingo.io';

        // Création de la session Stripe avec les données du panier
        $checkout_session = CheckoutSession::create([
            'line_items' => $productsForStripe,
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/order_success/stripe/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/order_cancel/stripe/{CHECKOUT_SESSION_ID}',
        ]);
        $order->setStripeSession($checkout_session->id);
        $em->flush();
        return $this->redirect($checkout_session->url);
    }

    #[Route('/order_send/airtel/{reference}', name: 'app_order_send')]
    public function send_airtel(OrderRepository $repository, $reference, EntityManagerInterface $em, HttpClientInterface $httpClientInterface): Response
    {
        $order = $repository->findOneByReference($reference);
        $response = $httpClientInterface->request(
            "POST",
            "https://stg.billing-easy.com/api/v1/merchant/e_bills",
            [
                'auth_basic' => ["Westerneat", "982d5642-e17f-47b4-b0ed-fb51acfbe756"],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => [
                    'amount' => 200,
                    'short_description' => "This is a payment",
                    'payer_msisdn' => "076496297",
                    'payer_name' => 'LOUPE',
                    'payer_email' => 'example@gmail.com',
                    'external_reference' => $order->getReference()
                ]
            ]
        );
        $content = $response->toArray()["e_bill"];
        $order->setBillId($content['bill_id']);
        $order->setStatus($content['state']);
        /* $global_array = [
            'amount' => 500,
            'short_description' => "This is a payment",
            'payer_msisdn' => "076496297",
            'payer_name' => 'LOUPE',
            'payer_email' => 'example@gmail.com',
            'external_reference' => $order->getReference()
        ];

        $content = json_encode($global_array);
        $curl = curl_init("https://lab.billing-easy.net/api/v1/merchant/e_bills");
        curl_setopt($curl, CURLOPT_USERPWD,  "username:password");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        $response = json_decode($json_response, true);
        curl_close($curl); */
        $em->flush();
        return $this->redirect("https://staging.billing-easy.net/?invoice=" . $content['bill_id'] . "&redirect_url=https://westerneat.osc-fr1.scalingo.io/order_success/billing");
        /* return $this->render('home/follow.html.twig', [
            'bill_id' => $content['bill_id']
        ]); */
    }

    #[Route('/order_success/stripe/{id}', name: 'app_order_success')]
    public function success($id, EntityManagerInterface $entityManager, CartStorage $cart): Response
    {
        //$order = $entityManager->getRepository(Order::class)->findBySession($id)[0];
        $cart->set([]);
        return $this->render('order/success.html.twig', [
            //'order' => $order
        ]);
    }

    #[Route('/order_cancel/stripe/{id}', name: 'app_order_cancel')]
    public function cancel($id, CartStorage $cart): Response
    {
        return $this->render('order/cancel.html.twig', ['items' => $cart->getItems(),]);
    }

    #[Route('/order_success/billing/{id?}', name: 'app_success_billing')]
    public function success_billing($id, CartStorage $cart): Response
    {
        $cart->set([]);
        return $this->render('order/success.html.twig', []);
    }

    #[Route('/order_cancel/billing/{id}', name: 'app_cancel_billing')]
    public function cancel_billing($id, CartStorage $cart): Response
    {
        return $this->render('order/cancel.html.twig', ['items' => $cart->getItems(),]);
    }

    #[Route('/e_billing__callback', name: 'app_e_billing')]
    public function ebilling(Request $request, OrderRepository $repository, EntityManagerInterface $em, LoggerInterface $logger, HttpClientInterface $httpClientInterface) : JsonResponse
    {
        $client = new Client([
            'base_uri' => 'https://staging.billing-easy.net/shap/api/v1/merchant/',
        ]);
	$data = array(
            'api_id' => 'e80390f1f2f4436',
            'api_secret' => 'ade3c8-d784d4-99e989-c4ff4e-731f31'
        );
        $data_string = json_encode($data);

        $res = $client->post('auth', [
            'body' => $data_string,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
        $accesstoken = json_decode($res->getBody()->getContents());


        $order = $repository->findOneByReference($request->request->get('reference'));
        $order->setStatus("paid");
        $em->flush();

        $logger->debug("Get the ref : {ref}", ['ref' => $request->request->all()]);
        $products = $order->getOrderDetails()->getValues();
	return $this->json($access_token);

        /* try{
            foreach ($products as $item) {
                $data = array(
                    'payment_system_name' => 'airtelmoney',
                    'payout' => '{"amount":350,"payee_msisdn":"076496297","payout_type":"cashback","external_reference":"' . $order->getReference() . '"}'
                );
                $data_string = json_encode($data);
                $resf = $client->post('payout', [
                    'body' => $data_string,
                    'headers' => [
                        "Content-Type" => "application/json",
                        "Authorization" => "Bearer "
                    ]
                ]);
            }
    
            //return $this->json($resf->getBody()->getContents());
        }catch(RequestException $e){
            var_dump($e);
            //return $this->json($e->getRequest());
        } */
        //$order->setBillId($request->request->get('state'))

        /* return $this->json(['amount' => $request->request->get('amount'), 'referencer' => $request->request->get('reference')]); */
    }
}
