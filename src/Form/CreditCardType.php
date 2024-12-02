<?php

namespace App\Form;

use App\Entity\CreditCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Credit Card Number',
                'attr' => ['placeholder' => 'Enter 16-digit card number'],
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'Expiration Date (MM/YY)',
                'attr' => ['placeholder' => 'MM/YY'],
            ])
            ->add('cvv', TextType::class, [
                'label' => 'CVV',
                'attr' => ['placeholder' => '3-digit CVV'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreditCard::class,
        ]);
    }
}
