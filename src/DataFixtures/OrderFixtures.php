<?php

namespace App\DataFixtures;

use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Enum\OrderStatus;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $order = new Order();

            $order->setReference('ORD-' . strtoupper(uniqid()));

            $order->setCreatedAt((new \DateTime())->modify('-' . mt_rand(0, 30) . ' days'));

            $statuses = OrderStatus::cases();
            $order->setStatus($statuses[array_rand($statuses)]);

            $userReference = 'user-' . ($i % 5); 
            $order->setUser($this->getReference($userReference));

            $manager->persist($order);

            $this->addReference('order-' . $i, $order);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class, 
        ];
    }
}
