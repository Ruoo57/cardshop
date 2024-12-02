<?php

namespace App\DataFixtures;

use App\Entity\OrderItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $orderItem = new OrderItem();

            $orderItem->setQuantity(mt_rand(1, 10));

            $orderItem->setProductPrice(mt_rand(10, 100));

            $productReference = 'product-' . ($i % 4); 
            $orderItem->setProduct($this->getReference($productReference));

            $orderReference = 'order-' . ($i % 20); 
            $orderItem->setOrder($this->getReference($orderReference));

            $manager->persist($orderItem);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            OrderFixtures::class,
        ];
    }
}
