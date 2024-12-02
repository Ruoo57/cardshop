<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Enum\ProductStatus;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            [
                'name' => 'Boule de Feu',
                'description' => 'Inflige 6 points de dégâts.',
                'price' => 5.0,
                'stock' => 20,
                'category' => 'commun',
            ],
            [
                'name' => 'Blizzard',
                'description' => 'Inflige 2 points de dégâts et gèle tous les serviteurs adverses',
                'price' => 10.0,
                'stock' => 15,
                'category' => 'rare',
            ],
            [
                'name' => 'Stitched Giant',
                'description' => 'Cost (1) less for each Corpse you have spent this game',
                'price' => 20.0,
                'stock' => 10,
                'category' => 'epique',
            ],
            [
                'name' => 'Aile de Mort',
                'description' => 'Cri de guerre : inflige 12 points de dégâts à tous les autres serviteurs.',
                'price' => 50.0,
                'stock' => 5,
                'category' => 'legendaire',
            ],
        ];

        foreach ($products as $index => $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setStock($data['stock']);
            $product->setCategory($this->getReference('category-' . $data['category']));
            $product->setStatus(ProductStatus::AVAILABLE);

            $manager->persist($product);

            $this->addReference('product-' . $index, $product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
