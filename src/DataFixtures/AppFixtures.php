<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de quelques produits de test
        $productsData = [
            ['name' => 'Product 1'],
            ['name' => 'Product 2'],
            ['name' => 'Product 3'],
        ];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data['name']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
