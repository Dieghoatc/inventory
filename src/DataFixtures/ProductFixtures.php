<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductWarehouse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public const PRODUCT_KF_01 = 'kf-01';
    public const PRODUCT_KF_02 = 'kf-02';
    public const PRODUCT_KF_03 = 'kf-03';

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
            [
                'code' => 'KF-02',
                'title' => 'KF-02',
                'price' => '150.00',
            ],
            [
                'code' => 'KF-03',
                'title' => 'KF-03',
                'price' => '200.00',
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

            if ('KF-01' === $item['code']) {
                $this->addReference(self::PRODUCT_KF_01, $product);
            }

            if ('KF-02' === $item['code']) {
                $this->addReference(self::PRODUCT_KF_02, $product);
            }

            if ('KF-03' === $item['code']) {
                $this->addReference(self::PRODUCT_KF_03, $product);
            }
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
