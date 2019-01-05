<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createOrder($manager);
    }

    protected function createOrder(ObjectManager $manager): void
    {
        $items = [
            [
                'code' => 'W00001',
                'source' => Order::SOURCE_PHONE,
                'status' => Order::STATUS_CREATED,
                'quantity' => 10,
            ],
            [
                'code' => 'W00002',
                'source' => Order::SOURCE_PHONE,
                'status' => Order::STATUS_INVOICED,
                'quantity' => 15,
            ],
            [
                'code' => 'W00003',
                'source' => Order::SOURCE_PHONE,
                'status' => Order::STATUS_READY_TO_SEND,
                'quantity' => 20,
            ],
            [
                'code' => 'W00004',
                'source' => Order::SOURCE_WEB,
                'status' => Order::STATUS_CREATED,
                'quantity' => 25,
            ],
            [
                'code' => 'W00005',
                'source' => Order::SOURCE_WEB,
                'status' => Order::STATUS_INVOICED,
                'quantity' => 30,
            ],
            [
                'code' => 'W00006',
                'source' => Order::SOURCE_WEB,
                'status' => Order::STATUS_READY_TO_SEND,
                'quantity' => 35,
            ],
        ];

        foreach ($items as $item) {
            $order = new Order();
            $order->setCode($item['code']);
            $order->setStatus($item['status']);
            $order->setSource($item['source']);
            $order->setCustomer($this->getReference(CustomerFixtures::CUSTOMER));
            $order->setWarehouse($this->getReference(WarehouseFixtures::WAREHOUSE_BOGOTA));
            $manager->persist($order);

            /** Attaching products to this order */
            $orderProduct = new OrderProduct();
            $orderProduct->setOrder($order);
            $orderProduct->setProduct($this->getReference(ProductFixtures::PRODUCT_KF_01));
            $orderProduct->setQuantity($item['quantity']);
            $manager->persist($orderProduct);

            /** Attaching products to this order */
            $orderProduct2 = new OrderProduct();
            $orderProduct2->setOrder($order);
            $orderProduct2->setProduct($this->getReference(ProductFixtures::PRODUCT_KF_02));
            $orderProduct2->setQuantity($item['quantity']);
            $manager->persist($orderProduct2);

            /** Attaching products to this order */
            $orderProduct3 = new OrderProduct();
            $orderProduct3->setOrder($order);
            $orderProduct3->setProduct($this->getReference(ProductFixtures::PRODUCT_KF_03));
            $orderProduct3->setQuantity($item['quantity']);
            $manager->persist($orderProduct3);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
            WarehouseFixtures::class,
            ProductFixtures::class,
        ];
    }
}
