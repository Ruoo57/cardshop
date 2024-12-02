<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\LiveComponentInterface;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Product;

class Cart implements LiveComponentInterface
{
    use DefaultActionTrait;

    private SessionInterface $session;
    private EntityManagerInterface $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    
    public static function getComponentName(): string
    {
        return 'cart'; 
    }

    
    public function initialize(array $data = []): void
    {
        if (!$this->session->has('cart')) {
            $this->session->set('cart', []); 
        }
    }

    
    public function getCartDetails(): array
    {
        $cart = $this->session->get('cart', []);
        $cartDetails = [];
        $totalPrice = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $cartDetails[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'total' => $product->getPrice() * $quantity,
                ];
                $totalPrice += $product->getPrice() * $quantity;
            }
        }

        return [
            'details' => $cartDetails, 
            'totalPrice' => $totalPrice, 
        ];
    }

   
    public function getTemplate(): string
    {
        return 'components/cart.html.twig'; 
    }

    
    public function getData(): array
    {
        return $this->getCartDetails();
    }
}
