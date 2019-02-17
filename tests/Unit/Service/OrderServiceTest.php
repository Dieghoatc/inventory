<?php

namespace App\Tests\Unit\Service;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Services\OrderService;
use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

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
        $customer = $this->getCustomerByEmail('jose.perez@example.com');
        $warehouse = $this->getWarehouseByName('Usa');

        $productA = $this->createProduct($warehouse, 'ADD-NEW-KF-01');
        $productB = $this->createProduct($warehouse, 'ADD-NEW-KF-02');

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

        $user = $this->getUserByEmail('sbarbosa115@gmail.com');
        $orderCreated = $orderService->add($orderItem, $user);

        $this->assertArrayHasKey('order', $orderCreated);
        $this->assertArrayHasKey('customer', $orderCreated['order']);
        $this->assertArrayHasKey('comments', $orderCreated['order']);
        $this->assertArrayHasKey('warehouse', $orderCreated['order']);
        $this->assertCount(2, $orderCreated['products']);
        $this->assertCount(2, $orderCreated['order']['comments']);
    }

    public function testRemoveOrder(): void
    {
        $codeToTest = 'TEST-TO-REMOVE-0001';
        /** @var $orderService OrderService */
        $orderService = $this->client->getContainer()->get(OrderService::class);
        $order = $this->createOrder(['code' => $codeToTest]);
        $this->assertInstanceOf(Order::class, $order);

        $orderService->deleteOrder($order);

        $order = $this->getOrderByCode($codeToTest);
        $this->assertNull($order);
    }

    public function testStatus(): void
    {
        $codeToTest = 'TEST-TO-STATUS-0001';
        $order = $this->createOrder(['code' => $codeToTest]);
        $this->assertCount(1, $order->getOrderStatuses());
    }
}
