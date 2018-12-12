<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductWarehouse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createProducts($manager);
    }

    protected function createProducts(ObjectManager $manager): void
    {
        $items = [
            [
                'code' => 'KF-01',
                'title' => 'KF-01',
                'price' => '100.00',
            ],
        ];

        foreach ($items as $item) {
            $product = new Product();
            $product->setCode($item['code']);
            $product->setTitle($item['title']);
            $product->setPrice($item['price']);
            $manager->persist($product);

            $productWarehouse = new ProductWarehouse();
            $productWarehouse->setProduct($product);
            $productWarehouse->setStatus(1);
            $productWarehouse->addQuantity(100);
            $productWarehouse->setWarehouse($this->getReference(WarehouseFixtures::WAREHOUSE_BOGOTA));
            $manager->persist($productWarehouse);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            WarehouseFixtures::class,
        ];
    }
}
