<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $imagePaths = [
            'image1.jpeg',
            'image2.jpeg',
            'image3.jpeg',
            'image4.jpeg',
        ];

        foreach ($imagePaths as $key => $fileName) {
            $image = new Image();
            $image->setUrl('/images/' . $fileName); 

            $productReference = 'product-' . $key; 
            if ($this->hasReference($productReference)) {
                $product = $this->getReference($productReference);
                $image->setProduct($product);
                $product->setImage($image);
            }

            $manager->persist($image);

            $this->addReference('image-' . $key, $image);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
        ];
    }
}
