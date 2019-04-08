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

        $this->assertArrayHasKey('customer', $orderCreated);
        $this->assertArrayHasKey('comments', $orderCreated);
        $this->assertArrayHasKey('warehouse', $orderCreated);
        $this->assertCount(2, $orderCreated['products']);
        $this->assertCount(2, $orderCreated['comments']);
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

    public function testUpdateStatusTwoTimes(): void
    {
        $codeToTest = 'TEST-TO-STATUS-99881';
        $order = $this->createOrder(['code' => $codeToTest]);

        $order->setStatus(Order::STATUS_PROCESSED);

        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $manager->persist($order);
        $manager->flush();


        $this->assertCount(2, $order->getOrderStatuses());
    }

    public function testOrderMarkAsCompletedSuccess(): void
    {
        $initialQuantity = 100;
        $warehouse = $this->getWarehouseByName('Colombia');
        $productA = $this->createProduct($warehouse, 'TEST-COMPLETE-ORDER-A', $initialQuantity);
        $productB = $this->createProduct($warehouse, 'TEST-COMPLETE-ORDER-B', $initialQuantity);

        $orderData = [
            'code' => 'UNIT-TEST-COMPLETE-001514',
            'products' => [
                [
                    'uuid' => $productA->getUuid(),
                    'quantity' => 25,
                ],
                [
                    'uuid' => $productB->getUuid(),
                    'quantity' => 75,
                ],
            ],
        ];
        $order = $this->createOrder($orderData);

        //Status that trigger the warehouse discount
        $order->setStatus(Order::STATUS_SENT);
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $manager->persist($order);
        $manager->flush();

        $productAOnWarehouse = $this->getProductWarehouse($productA, $warehouse);
        $productBOnWarehouse = $this->getProductWarehouse($productB, $warehouse);

        $this->assertEquals(75, $productAOnWarehouse->getQuantity());
        $this->assertEquals(25, $productBOnWarehouse->getQuantity());
    }

    public function testCreateOrderThenUpdateIt(): void
    {
        $caseOrderCode = 'UNIT-TEST-NEW-EDIT-CASE-CODE01';
        /** @var $orderService OrderService */
        $orderService = $this->client->getContainer()->get(OrderService::class);
        // Customer taken from CustomerFixture
        $customer = $this->getCustomerByEmail('jose.perez@example.com');
        $warehouse = $this->getWarehouseByName('Usa');

        $productA = $this->createProduct($warehouse, 'ADD-NEW-EDIT-KF-A');
        $productB = $this->createProduct($warehouse, 'ADD-NEW-EDIT-KF-B');
        $productC = $this->createProduct($warehouse, 'ADD-NEW-EDIT-KF-C');

        $orderData = [
            'code' => $caseOrderCode,
            'comment' => 'ORDER_COMMENT_WHEN_IT_WAS_CREATED',
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
                [
                    'uuid' => $productC->getUuid(),
                    'quantity' => 30,
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

        $orderAsEntity = $this->createOrder($orderData);
        $order = $orderService->getOrderAsArray($orderAsEntity);
        $this->assertArrayHasKey('products', $order);
        $productD = $this->createProduct($warehouse, 'ADD-NEW-EDIT-KF-D');

        $order['products'][0]['quantity'] = 11;
        unset($order['products'][2]);
        $order['products'][] = [
            'uuid' => $productD->getUuid(),
            'quantity' => 40,
        ];

        $orderService->update($orderAsEntity, $order);
        $orderUpdate = $this->getOrderByCode($caseOrderCode);

        $this->assertCount(3, $orderUpdate->getProducts());
        $this->assertTrue($orderUpdate->isProductInOrder($productA));
        $this->assertTrue($orderUpdate->isProductInOrder($productB));
        $this->assertTrue($orderUpdate->isProductInOrder($productD));
        $this->assertFalse($orderUpdate->isProductInOrder($productC));
    }
}
