<?php

namespace App\Controller;

use App\Entity\CreditCard;
use App\Form\CreditCardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreditCardController extends AbstractController
{
    #[Route('/credit-card', name: 'app_credit_card')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add a credit card.');
        }

        $creditCard = new CreditCard();
        $form = $this->createForm(CreditCardType::class, $creditCard);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creditCard->setUser($user);

            $entityManager->persist($creditCard);
            $entityManager->flush();

            $this->addFlash('success', 'Credit card added successfully!');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/credit_card/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/credit-card/edit/{id}', name: 'app_edit_credit_card')]
    public function editCreditCard(
        Request $request,
        CreditCard $creditCard,
        EntityManagerInterface $entityManager
    ): Response {
        if ($creditCard->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have permission to edit this credit card.');
        }

        $form = $this->createForm(CreditCardType::class, $creditCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'The credit card has been updated successfully.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/credit_card/edit.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Update',
        ]);
    }

    #[Route('/credit-card/delete/{id}', name: 'app_delete_credit_card', methods: ['POST'])]
    public function deleteCreditCard(
        Request $request,
        CreditCard $creditCard,
        EntityManagerInterface $entityManager
    ): Response {
        if ($creditCard->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have permission to delete this credit card.');
        }

        if ($this->isCsrfTokenValid('delete_credit_card_' . $creditCard->getId(), $request->request->get('_token'))) {
            $entityManager->remove($creditCard);
            $entityManager->flush();
            $this->addFlash('success', 'The credit card has been deleted successfully.');
        }

        return $this->redirectToRoute('app_profile');
    }
}
