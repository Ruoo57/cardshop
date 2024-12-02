<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Image;
use App\Entity\Address;
use App\Form\ProductFormType;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ImageUploader;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Repository\ProductRepository;
use App\Form\AddressType;
use App\Repository\CategoryRepository; 
use App\Enum\ProductStatus;
use App\Entity\User;

class AdminController extends AbstractController
{
    #[Route('/admin/product/new', name: 'admin_product_new')]
    public function newProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($product->getStock() < 0) {
                $this->addFlash('error', 'Le stock ne peut pas être négatif.');
            } elseif ($form->isValid()) {
                /** @var UploadedFile|null $uploadedFile */
                $uploadedFile = $form->get('image')->getData();

                if ($uploadedFile instanceof UploadedFile) {
                    $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                    $uploadedFile->move($uploadDir, $fileName);

                    $image = new Image();
                    $image->setUrl('/uploads/' . $fileName);
                    $product->setImage($image);
                }

                $entityManager->persist($product);
                $entityManager->flush();

                $this->addFlash('success', 'Produit créé avec succès.');
                return $this->redirectToRoute('shop_home');
            }
        }

        return $this->render('admin/shop/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('admin/shop', name: 'shop_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('admin/shop/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/product/edit/{id}', name: 'admin_product_edit')]
    public function editProduct(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($product->getStock() < 0) {
                $this->addFlash('error', 'Le stock ne peut pas être négatif.');
            } elseif ($form->isValid()) {
                /** @var UploadedFile|null $uploadedFile */
                $uploadedFile = $form->get('image')->getData();

                if ($uploadedFile) {
                    if ($product->getImage()) {
                        $oldImagePath = $this->getParameter('kernel.project_dir') . '/public' . $product->getImage()->getUrl();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                    $uploadedFile->move($uploadDir, $fileName);

                    $image = new Image();
                    $image->setUrl('/uploads/' . $fileName);
                    $product->setImage($image);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Le produit a été modifié avec succès.');
                return $this->redirectToRoute('shop_home');
            }
        }

        return $this->render('admin/shop/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/admin/product/delete/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function deleteProduct(int $id, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        foreach ($product->getOrderItems() as $orderItem) {
            $entityManager->remove($orderItem);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a été supprimé avec succès.');
        return $this->redirectToRoute('shop_home');
    }

    #[Route('/admin/profile', name: 'app_admin_profile')]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_admin_login');
        }

        $addresses = $entityManager->getRepository(Address::class)->findBy(['user' => $user]);

        return $this->render('admin/profile/index.html.twig', [
            'user' => $user,
            'addresses' => $addresses,
        ]);
    }

    #[Route('admin/address/edit/{id}', name: 'app_admin_edit_address')]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Address updated successfully!');
            return $this->redirectToRoute('app_admin_profile');
        }

        return $this->render('admin/profile/address_form.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Edit',
        ]);
    }

    #[Route('admin/address/add', name: 'app_admin_add_address')]
    public function addAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $address = new Address();
        $address->setUser($user);

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Address added successfully!');
            return $this->redirectToRoute('app_admin_profile');
        }

        return $this->render('admin/profile/address_form.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Add',
        ]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $categoriesWithProductCount = $categoryRepository->getCategoriesWithProductCount();

        $orders = $entityManager->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->join('o.user', 'u')  
            ->orderBy('o.createdAt', 'DESC')  
            ->setMaxResults(5)  
            ->getQuery()
            ->getResult();

        $inStockCount = $entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->where('p.stock > 0')  
            ->getQuery()
            ->getResult();

        $outOfStockCount = $entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->where('p.stock <= 0 OR p.stock IS NULL') 
            ->getQuery()
            ->getResult();

        $preOrderCount = $entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', ProductStatus::PRE_ORDER) 
            ->getQuery()
            ->getResult();

        $totalProducts = $entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $inStockRatio = $totalProducts > 0 ? (count($inStockCount) / $totalProducts) * 100 : 0;
        $outOfStockRatio = $totalProducts > 0 ? (count($outOfStockCount) / $totalProducts) * 100 : 0;
        $preOrderRatio = $totalProducts > 0 ? (count($preOrderCount) / $totalProducts) * 100 : 0;

        $salesPerMonth = [];
        $currentDate = new \DateTime();

        for ($i = 0; $i < 12; $i++) {
            $startDate = (clone $currentDate)->modify("-$i month")->modify('first day of this month')->setTime(0, 0);
            $endDate = (clone $startDate)->modify('last day of this month')->setTime(23, 59, 59);

            $totalSalesForMonth = $entityManager->getRepository(Order::class)
                ->createQueryBuilder('o')
                ->select('SUM(oi.quantity * oi.productPrice)')  
                ->join('o.orderItems', 'oi')  
                ->where('o.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery()
                ->getSingleScalarResult();

            $salesPerMonth[$startDate->format('Y-m')] = $totalSalesForMonth ? (float) $totalSalesForMonth : 0;
        }

        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/dashboard/index.html.twig', [
            'categoriesWithProductCount' => $categoriesWithProductCount,
            'orders' => $orders, 
            'inStockCount' => count($inStockCount),
            'outOfStockCount' => count($outOfStockCount),
            'preOrderCount' => count($preOrderCount),
            'inStockRatio' => $inStockRatio,
            'outOfStockRatio' => $outOfStockRatio,
            'preOrderRatio' => $preOrderRatio,
            'salesPerMonth' => $salesPerMonth, 
            'users' => $users,  
        ]);
    }
}
