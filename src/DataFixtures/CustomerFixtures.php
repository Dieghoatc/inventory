<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{
    public const CUSTOMER = 'customer';
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createWarehouses($manager);
    }

    protected function createWarehouses(ObjectManager $manager): void
    {
        $items = [
            [
                'first_name' => 'Jose',
                'last_name' => 'Perez',
                'email' => 'jose.perez@example.com',
                'phone' => '+57 3002825566',
            ],
        ];

        foreach ($items as $item) {
            $customer = new Customer();
            $customer->setFirstName($item['first_name']);
            $customer->setLastName($item['last_name']);
            $customer->setEmail($item['email']);
            $customer->setPhone($item['phone']);
            $manager->persist($customer);

            $this->addReference(self::CUSTOMER, $customer);
        }
        $manager->flush();
    }
}
