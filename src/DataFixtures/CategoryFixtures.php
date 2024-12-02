<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORIES = [
        'Commun' => 'commun',
        'Rare' => 'rare',
        'Épique' => 'epique',
        'Légendaire' => 'legendaire',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $label => $reference) {
            $category = new Category();
            $category->setRarity($label);
            $manager->persist($category);

            $this->addReference('category-' . $reference, $category);
        }

        $manager->flush();
    }
}
