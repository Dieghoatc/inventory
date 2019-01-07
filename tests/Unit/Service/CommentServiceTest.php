<?php

namespace App\Tests\Unit\Service;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Services\CommentService;
use App\Services\OrderService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentServiceTest extends WebTestCase
{
    public $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function createOrder(): array
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
            'code' => 'UNIT-TEST-CODE02',
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
                    'code' => $productA->getCode(),
                    'quantity' => 10,
                ],
                [
                    'code' => $productB->getCode(),
                    'quantity' => 20,
                ],
            ],
            'comments' => [
                ['content' => 'PHP Unit test comment A.'],
                ['content' => 'PHP Unit test comment B.'],
            ],
        ];

        $user = $this->client->getContainer()->get('doctrine')
            ->getRepository(User::class)->findOneBy(['username' => 'sbarbosa115']);

        return $orderService->addOrder($orderItem, $user);
    }

    public function testSyncComments(): void
    {
        $orderItem = $this->createOrder();

        /** @var $user User */
        $user = $this->client->getContainer()->get('doctrine')
            ->getRepository(User::class)->findOneBy(['username' => 'sbarbosa115']);

        $comments = $orderItem['order']['comments'];
        foreach ($comments as $commentKey => $comment) {
            $comments[$commentKey]['content'] = "{$comment['content']} Edited";
        }

        /** @var $order Order */
        $order = $this->client->getContainer()->get('doctrine')->getRepository(Order::class)->find($orderItem['order']['id']);

        /** @var $commentService CommentService */
        $commentService = $this->client->getContainer()->get(CommentService::class);
        $commentService->syncComments($comments, $user, $order);

        $this->assertEquals(2, $order->getComments()->count());
        foreach ($order->getComments() as $comment) {
            $commentEdited = strpos($comment->getContent(), 'Edited');
            $this->assertNotFalse($commentEdited);
        }
    }
}
