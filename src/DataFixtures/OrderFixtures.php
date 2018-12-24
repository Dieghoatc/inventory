<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Order;
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
            ],
            [
                'code' => 'W00002',
                'source' => Order::SOURCE_PHONE,
                'status' => Order::STATUS_INVOICED,
            ],
            [
                'code' => 'W00003',
                'source' => Order::SOURCE_PHONE,
                'status' => Order::STATUS_READY_TO_SEND,
            ],
            [
                'code' => 'W00004',
                'source' => Order::SOURCE_WEB,
                'status' => Order::STATUS_CREATED,
            ],
            [
                'code' => 'W00005',
                'source' => Order::SOURCE_WEB,
                'status' => Order::STATUS_INVOICED,
            ],
            [
                'code' => 'W00006',
                'source' => Order::SOURCE_WEB,
                'status' => Order::STATUS_READY_TO_SEND,
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
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
            WarehouseFixtures::class,
        ];
    }
}
