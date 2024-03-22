<?php
namespace App\Twig\Components;

use App\Utils\CartStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class RowCart extends AbstractController{

    use DefaultActionTrait;

    #[LiveProp]
    public array $product;

    #[LiveProp]
    public int $quantity = 1;

    private CartStorage $cart;

    public function __construct(CartStorage $cart)
    {
        $this->cart = $cart;
    }

    public function mount(array $product)
    {
        $this->product = $product;
    }
    
    #[LiveAction]
    public function updateItem(#[LiveArg('qty')] int $qty) : void {
        $item = $this->cart->getItem($this->product["id"]);
        $item["quantity"] += $qty;
        $this->quantity += $qty; 
        $this->cart->update($this->product["id"], $item);
    }

    
    public function item() : int {
        return $this->quantity;
    }

    public function getRandomNumber(): int
    {
        return rand(0, 1000);
    }
}