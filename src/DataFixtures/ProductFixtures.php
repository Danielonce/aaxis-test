<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $clothingTypes = ['shirt', 't-shirt', 'pants', 'shoes', 'hat', 'jacket', 'socks', 'scarf', 'gloves', 'shorts'];

        for ($i = 1; $i <= 50; $i++) {
            $product = new Product();
            
            $sku = 'SKU-' . $i;

            $randomClothingType = $clothingTypes[array_rand($clothingTypes)];

            $product->setSku($sku);
            $product->setProductName($randomClothingType);
            $product->setDescription("desc $i");
            $product->setCreatedAt(new \DateTime());
            $manager->persist($product);
        }

        $manager->flush();
    }
}

