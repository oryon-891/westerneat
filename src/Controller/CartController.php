<?php

namespace App\Controller;

use App\Entity\Carrier;
use App\Entity\Product;
use App\Utils\CartStorage;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartStorage $cart): Response
    {

        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getItems(),
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add($id, CartStorage $cart, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->findOneById($id);
        $cart_array = $cart->getItems();
        $item = $product->toArray();
        unset($item["subtitle"]);
        unset($item["description"]);
        unset($item["is_best"]);
        unset($item["category"]);
        unset($item["ddice"]);
        $item["quantity"] = 1;
        $cart_array[] = $item;
        $cart->set($cart_array);
        return $this->redirect('/cart');
    }


    #[Route('/cart/update/{id}/{qty}', name: 'app_cart_update')]
    public function update($id, $qty, CartStorage $cart, SerializerInterface $serializer): Response
    {
        $item = $cart->getItem($id);
        $item["quantity"] += ($qty == "true" ? 1 : -1); 

        $cart->update($id, $item);

        return $this->json(
            json_decode(
                $serializer->serialize($cart->getItems()[0]["id"], 'json'),
                JSON_OBJECT_AS_ARRAY
            )
        );

        /* $query = $request->quer('query');

        $query = $entityManager->getRepository(City::class)->findById($query);

        $query = $entityManager->getRepository(Distinct::class)->findByCity($query);

        return $this->json(
            json_decode(
                $serializer->serialize($query, 'json'),
                JSON_OBJECT_AS_ARRAY
            )
        ); */
    }

}
