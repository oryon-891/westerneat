<?php
namespace App\Manager;

use App\Utils\CartStorage;

class CartManager
{
    /**
     * @var CartSessionStorage
     */
    private $cartSessionStorage;


    /**
     * CartManager constructor.
     *
     * @param CartSessionStorage $cartStorage
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        CartStorage $cartStorage,
    ) {
        $this->cartSessionStorage = $cartStorage;
    }

    /**
     * Gets the current cart.
     * 
     * @return 
     */
    public function getCurrentCart() 
    {
        $cart = $this->cartSessionStorage->getCart();

        return $cart;
    }
}