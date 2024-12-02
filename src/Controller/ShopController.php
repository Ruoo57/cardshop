<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Image;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\CreditCard;
use App\Repository\ProductRepository;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function shop(
        Request $request,
        SessionInterface $session,
        ProductRepository $productRepository
    ): Response {
        $query = $request->query->get('query', '');

        $products = $productRepository->findByQuery($query);

        $cart = $session->get('cart', []);
        $cartDetails = [];
        $totalPrice = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product) {
                $cartDetails[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'total' => $product->getPrice() * $quantity,
                ];
                $totalPrice += $product->getPrice() * $quantity;
            }
        }

        return $this->render('components/cart.html.twig', [
            'products' => $products,
            'query' => $query,
            'cartDetails' => $cartDetails,
            'totalPrice' => $totalPrice,
        ]);
    }

    #[Route('/shop/buy/{id}', name: 'app_shop_buy', methods: ['POST'])]
    public function buy(int $id, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product || $product->getStock() <= 0) {
            return new Response('Produit indisponible.', 400);
        }

        $cart = $session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $session->set('cart', $cart);

        $cartDetails = [];
        $totalPrice = 0;
        foreach ($cart as $productId => $quantity) {
            $cartProduct = $entityManager->getRepository(Product::class)->find($productId);
            if ($cartProduct) {
                $cartDetails[] = [
                    'product' => $cartProduct,
                    'quantity' => $quantity,
                    'total' => $cartProduct->getPrice() * $quantity,
                ];
                $totalPrice += $cartProduct->getPrice() * $quantity;
            }
        }

        $cartHtml = $this->renderView('components/cart.html.twig', [
            'products' => $entityManager->getRepository(Product::class)->findAll(),
            'cartDetails' => $cartDetails,
            'totalPrice' => $totalPrice,
        ]);

        return new Response($cartHtml);
    }

    #[Route('/cart', name: 'app_cart')]
    public function showCart(SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $cart = $session->get('cart', []);

        $cartDetails = [];
        $totalPrice = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($productId);

            if ($product) {
                $cartDetails[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'total' => $product->getPrice() * $quantity,
                ];
                $totalPrice += $product->getPrice() * $quantity;
            }
        }

        return $this->render('components/cart.html.twig', [
            'cartDetails' => $cartDetails,
            'totalPrice' => $totalPrice,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function addToCart(int $id, SessionInterface $session, EntityManagerInterface $entityManager): JsonResponse
    {
        $cart = $session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $session->set('cart', $cart);

        $totalPrice = $this->calculateTotalPrice($cart, $entityManager);
        return new JsonResponse([
            'success' => true,
            'cartCount' => array_sum($cart),
            'totalPrice' => $totalPrice,
        ]);
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function removeFromCart(
        $id,
        SessionInterface $session,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $token = new CsrfToken('remove_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
        }

        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);

            $cartCount = array_sum($cart);
            return new JsonResponse([
                'success' => true,
                'cartCount' => $cartCount,
            ]);
        }

        return new JsonResponse(['success' => false], 404);
    }

    private function calculateTotalPrice(array $cart, EntityManagerInterface $entityManager): float
    {
        $totalPrice = 0;
        foreach ($cart as $productId => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $totalPrice += $product->getPrice() * $quantity;
            }
        }
        return $totalPrice;
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout')]
    public function checkout(SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); 
        $cart = $session->get('cart', []); 

        if (empty($cart)) {
            $this->addFlash('error', 'Votre caddie est vide.');
            return $this->redirectToRoute('app_cart'); 
        }

        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTime());
        $order->setStatus(OrderStatus::PREPARING);
        $order->setReference(uniqid('order_'));

        $totalPrice = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($productId);

            if ($product && $product->getStock() >= $quantity) {
                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setProductPrice($product->getPrice());
                $orderItem->setOrder($order);

                $order->addOrderItem($orderItem);

                $product->setStock($product->getStock() - $quantity);
                $entityManager->persist($product);

                $totalPrice += $product->getPrice() * $quantity;
                $entityManager->persist($orderItem);
            } else {
                $this->addFlash('error', 'Un ou plusieurs produits sont en rupture de stock.');
                return $this->redirectToRoute('app_cart'); 
            }
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $session->remove('cart');

        $this->addFlash('success', 'Votre commande a été passée avec succès.');

        return $this->redirectToRoute('app_order_confirmation'); 
    }

    #[Route('/order/confirmation', name: 'app_order_confirmation')]
    public function orderConfirmation(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); 

        $orders = $entityManager->getRepository(Order::class)->findBy(['user' => $user]);

        return $this->render('user/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
}
