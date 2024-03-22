<?php

namespace App\Utils;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartStorage{

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The request stack.
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * The cart repository.
     *
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    public function set(array $items){
        $this->getSession()->set("cart", $items);
    }

    public function length() : ?int{
        return count($this->getSession()->get("cart"));
    }

    public function getItems(){
        return $this->getSession()->get("cart", []);
    }

    public function getItem($id){
        foreach($this->getItems() as $product){
            if($product["id"] == $id){
                return $product;
            }
        }
        return [];
    }


    public function update($id, $product){
        $cart_array = $this->getItems();
        for($i = 0; $i < count($cart_array); $i++){
            if($cart_array[$i]["id"] == $id){
                $cart_array[$i] = $product;
            }
        }
        $this->set($cart_array);
    }

    public function setExtra(array $items){
        $this->getSession()->set("extra", $items);
    }


    public function getExtra(){
        $ref = $this->getSession()->get("extra", []);
        return $ref["reference"];
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}

