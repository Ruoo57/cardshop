<?php

namespace App\Form;

use App\Entity\Product;
use App\Enum\ProductStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.product.name.label',
                'attr' => ['placeholder' => 'form.product.name.placeholder'],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'form.product.price.label',
                'currency' => 'EUR',
                'attr' => ['placeholder' => 'form.product.price.placeholder'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.product.description.label',
                'attr' => ['placeholder' => 'form.product.description.placeholder'],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'form.product.stock.label',
                'attr' => ['placeholder' => 'form.product.stock.placeholder'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'form.product.status.label',
                'choices' => [
                    'form.product.status.available' => ProductStatus::AVAILABLE,
                    'form.product.status.out_of_stock' => ProductStatus::OUT_OF_STOCK,
                    'form.product.status.pre_order' => ProductStatus::PRE_ORDER,
                ],
                'placeholder' => 'form.product.status.placeholder',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'rarity',
                'label' => 'form.product.category.label',
                'placeholder' => 'form.product.category.placeholder',
            ])
            ->add('image', FileType::class, [
                'label' => 'form.product.image.label',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'form.product.image.mime_types_message',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
