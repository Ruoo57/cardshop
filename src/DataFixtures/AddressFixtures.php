<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AddressFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $address = new Address();
            $address->setStreet("Street $i");
            $address->setPostalCode(rand(10000, 99999));
            $address->setCity("City $i");
            $address->setCountry("Country $i");

            $userReference = 'user-' . ($i % 5); 
            $address->setUser($this->getReference($userReference));

            $manager->persist($address);
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
