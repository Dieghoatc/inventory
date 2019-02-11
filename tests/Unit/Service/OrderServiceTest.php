<?php

namespace App\Tests\Unit\Service;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Services\OrderService;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderServiceTest extends WebTestCase
{
    /** @var $client Client */
    public $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAddOrder(): void
    {
        // Data for this test was taken from Fixtures.
        /** @var $orderService OrderService */
        $orderService = $this->client->getContainer()->get(OrderService::class);
        // Customer taken from CustomerFixture
        /** @var $customer Customer */
        $customer = $this->client->getContainer()->get('doctrine')
            ->getRepository(Customer::class)->findOneBy(['email' => 'jose.perez@example.com']);

        /** @var $warehouse Warehouse */
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Usa']);

        /** @var $productA Product */
        $productA = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)->findOneBy(['code' => 'KF-01']);

        /** @var $productB Product */
        $productB = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)->findOneBy(['code' => 'KF-02']);

        $orderItem = [
            'code' => 'UNIT-TEST-CODE01',
            'comment' => 'EMPTY TEST ORDER COMMENT',
            'paymentMethod' => Order::PAYMENT_CREDIT_CARD,
            'status' => Order::STATUS_CREATED,
            'source' => Order::SOURCE_WEB,
            'warehouse' => [
                'id' => $warehouse->getId(),
            ],
            'customer' => [
                'id' => $customer->getId(),
            ],
            'products' => [
                [
                    'uuid' => $productA->getUuid(),
                    'quantity' => 10,
                ],
                [
                    'uuid' => $productB->getUuid(),
                    'quantity' => 20,
                ],
            ],
            'comments' => [
                [
                    'content' => 'PHP Unit test comment A.',
                ],
                [
                    'content' => 'PHP Unit test comment B.',
                ],
            ],
        ];

        $user = $this->client->getContainer()->get('doctrine')
            ->getRepository(User::class)->findOneBy(['username' => 'sbarbosa115']);

        $orderCreated = $orderService->add($orderItem, $user);
        $this->assertArrayHasKey('order', $orderCreated);
        $this->assertArrayHasKey('customer', $orderCreated['order']);
        $this->assertArrayHasKey('comments', $orderCreated['order']);
        $this->assertArrayHasKey('warehouse', $orderCreated['order']);
    }
}
