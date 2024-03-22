<?php

namespace App\Controller;

use App\Entity\Carrier;
use App\Entity\City;
use App\Entity\Distinct;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Entity\User;
use App\Form\CarrierType;
use App\Repository\CityRepository;
use App\Utils\CartStorage;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SerializerSerializerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CartStorage $cart): Response
    {
        if(count($cart->getItems()) == 0){
            $cart->set([]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'items' => $cart->getItems()
        ]);
    }

    /* #[Route('/doom', name: 'app_home')]
    public function doom(): Response
    {
        return $this->render('home/follow.html.twig');
    } */

    #[Route('/vendors', name: 'app_vendors')]
    public function vendors(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager, CartStorage $cart, CityRepository $cityRepository): Response
    {
        if ($request->query->get('query') != null) {
            $query = $request->query->get('query');
            $users = $entityManager->getRepository(User::class)->findBySearch("VENDOR", $query);
            //$query = $entityManager->createQuery("SELECT u.firstname, u.location FROM App\Entity\User u WHERE u.location = :zone")->setParameter(':zone', $query);
        } else {
            $users = $entityManager->getRepository(User::class)->findByRoleExact("VENDOR");
        }

        $listing = [];
        foreach ($cityRepository->findAll() as $q) {
            //array_push($listing, join(", ", array($q["name"], $q["quarter"])));
            array_push($listing, array($q->getId(), $q->getName()));
        }

        $pagination = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1), // Get the current page number from the request
            12 // Number of items per page
        );

        return $this->render('home/vendors.html.twig', [
            'vendors' => $pagination,
            'listings' => $listing,
            'items' => $cart->getItems()
        ]);
    }

    #[Route('/products/{code?}', name: 'app_products')]
    public function products(Request $request, $code, CartStorage $cart, PaginatorInterface $paginator, EntityManagerInterface $entityManager)
    {
        /*if($zone){
         $products = $this->entityManager->getRepository(Product::class)->findAll();
       }else{
         $products = $this->entityManager->getRepository(Product::class)->findAll();
       }*/
        if ($code != null) {
            $products = $entityManager->getRepository(Product::class)->findByCode($code);
        } else {
            $products = $entityManager->getRepository(Product::class)->findAuthorized();
        }

        $pagination = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1), // Get the current page number from the request
            12 // Number of items per page
        );

        //$panier = $cart->getFull();

        return $this->render('home/products.html.twig', [
            'products' => $pagination,
            'items' => $cart->getItems()
            //'items' => $panier,
        ]);
    }

    #[Route('/product_detail/{id}', name: 'app_product')]
    public function detail(EntityManagerInterface $entityManager, CartStorage $cart, $id)
    {
        $product = $entityManager->getRepository(Product::class)->findOneById($id);
        $products = $entityManager->getRepository(Product::class)->findOneBy(['is_best' => true]);

        /* if (!$product) {
            return $this->redirectToRoute('app_products');
        } */

        return $this->render('home/product.html.twig', [
            'product' => $product,
            'products' => $products,
            'items' => $cart->getItems()
        ]);
    }

    #[Route('/cart', name: 'app_cart')]
    public function cart(CartStorage $cart)
    {
        return $this->render('home/cart.html.twig', [
            'cart' => $cart->getItems(),
            'items' => $cart->getItems()
        ]);
    }

    #[Route('/recap', name: 'app_recap')]
    public function recap(CartStorage $cart, EntityManagerInterface $entityManager, Request $request): Response
    {

        $form = $this->createForm(CarrierType::class);
        $form->handleRequest($request);

        $t = "";
        if ($form->isSubmitted() && $form->isValid()) {
            $carrier =  $entityManager->getRepository(Carrier::class)->findById($form->get('carriers')->getData());
            $order = new Order;
            $date = new \DateTime;
            $extra = array();

            $order
                ->setUsers($this->getUser())
                ->setStatus("Pending")
                ->setDelivered(0)
                ->setTotalPrice(intval($request->request->get("price")) + $carrier[0]->getCommission())
                ->setReference($date->format('YmdHis') . '-' . uniqid())
                ->setCarrierName($carrier[0]->getName());
            $entityManager->persist($order);

            foreach ($cart->getItems() as $detail) {
                $orderDetails = new OrderDetail();
                $orderDetails
                    ->setOrders($order)
                    ->setName($detail['name'])
                    ->setQuantity($detail['quantity'])
                    ->setPrice($detail['price']);
                $entityManager->persist($orderDetails);
            }
            $extra["reference"] = $order->getReference();
            $extra["commission"] = $carrier[0]->getCommission();

            $cart->setExtra($extra);
            $entityManager->flush();

            return $this->redirectToRoute('app_order');
        }

        $carriers = $entityManager->getRepository(Carrier::class)->findAll();
        return $this->render('cart/recap.html.twig', [
            'cart' => $cart->getItems(),
            'items' => $cart->getItems(),
            'carriers' => $carriers,
            'form' => $form,
        ]);
    }


    #[Route('/search', name: 'search')]
    public function search(Request $request, SerializerSerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        $query = $request->query->get('query');

        $query = $entityManager->getRepository(City::class)->findById($query);

        $query = $entityManager->getRepository(Distinct::class)->findByCity($query);

        return $this->json(
            json_decode(
                $serializer->serialize($query, 'json'),
                JSON_OBJECT_AS_ARRAY
            )
        );

        /* return new Response($serializer->serialize(
            $vendors,
            'json'
        )); */
    }
}
