<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail("user$i@gmail.com");
            $user->setFirstName("User$i");
            $user->setLastName("Test$i");
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            $manager->persist($user);

            $this->addReference('user-' . $i, $user);
        }

        for ($i = 0; $i < 2; $i++) {
            $admin = new User();
            $admin->setEmail("admin$i@gmail.com");
            $admin->setFirstName("Admin$i");
            $admin->setLastName("Test$i");
            $admin->setRoles(['ROLE_ADMIN']);

            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password');
            $admin->setPassword($hashedPassword);

            $manager->persist($admin);

            $this->addReference('admin-' . $i, $admin);
        }

        $manager->flush();
    }
}
