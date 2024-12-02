<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\CreditCard;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $addresses = $entityManager->getRepository(Address::class)->findBy(['user' => $user]);

        $creditCards = $entityManager->getRepository(CreditCard::class)->findBy(['user' => $user]);

        return $this->render('user/profile/index.html.twig', [
            'user' => $user,
            'addresses' => $addresses,
            'creditCards' => $creditCards,
        ]);
    }

    #[Route('/address/edit/{id}', name: 'app_edit_address')]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Address updated successfully!');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/profile/address_form.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Edit',
        ]);
    }

    #[Route('/address/add', name: 'app_add_address')]
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
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/profile/address_form.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Add',
        ]);
    }
}
