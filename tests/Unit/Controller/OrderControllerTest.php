<?php

namespace App\Tests\Unit\Controller;

use App\Tests\Unit\Utils\UserWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends UserWebTestCase
{

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @dataProvider getUrlsForRoleUpdate
     */
    public function testOkByAllRoutes(string $httpMethod, string $url): void
    {
        $this->logIn(['ROLE_UPDATE_ORDERS']);
        $this->client->request($httpMethod, $url);
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider getUrlsForRoleUpdate
     */
    public function testForbiddenNoRol(string $httpMethod, string $url): void
    {
        $this->logIn(['ROLE_USER']);
        $this->client->request($httpMethod, $url);
        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function getUrlsForRoleUpdate(): ?\Generator
    {
        yield ['GET', '/admin/order/'];
        yield ['GET', '/admin/order/all/1'];
        yield ['GET', '/admin/order/new'];
    }

    public function testCorrectRolesForUpdateOrderRoleOnIndex(): void
    {
        $this->logIn(['ROLE_UPDATE_ORDERS']);

        $crawler = $this->client->request('GET', '/admin/order/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertEquals('', $crawler->filter('#index-orders')->getNode(0)->getAttribute('data-can-delete'));
        $this->assertEquals('', $crawler->filter('#index-orders')->getNode(0)->getAttribute('data-can-sync'));
        $this->assertEquals('1', $crawler->filter('#index-orders')->getNode(0)->getAttribute('data-can-add'));

        $this->assertEquals(1, $crawler->filter('.sidebar.navbar-nav > .nav-item')->count());
    }

    public function testCorrectRolesForAdminForOnIndex(): void
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/admin/order/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertEquals('1', $crawler->filter('#index-orders')->getNode(0)->getAttribute('data-can-delete'));
        $this->assertEquals('1', $crawler->filter('#index-orders')->getNode(0)->getAttribute('data-can-sync'));
        $this->assertEquals('1', $crawler->filter('#index-orders')->getNode(0)->getAttribute('data-can-add'));

        $this->assertEquals(5, $crawler->filter('.sidebar.navbar-nav > .nav-item')->count());
    }

    public function testAddEditAndRemoveAnOrder(): void
    {
        $orderCode = 'CREATED_USING_A_CONTROLLER_01';
        $this->logIn(['ROLE_UPDATE_ORDERS']);
        $productA = $this->createProduct(null, 'ADD-NEW-EDIT-KF-A');
        $productB = $this->createProduct(null, 'ADD-NEW-EDIT-KF-B');
        $productC = $this->createProduct(null, 'ADD-NEW-EDIT-KF-C');
        $originalOrder = $this->createOrderStructure([
            'code' => $orderCode,
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
        ]);

        $this->client->request('POST', '/admin/order/create', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ], json_encode($originalOrder));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $lastOrder = $this->getLastAddedOrder();

        $productD = $this->createProduct(null, 'ADD-NEW-EDIT-KF-D');
        $originalOrder['products'][0]['quantity'] = 11;
        unset($originalOrder['products'][1]);
        $originalOrder['products'][] = [
            'uuid' => $productD->getUuid(),
            'quantity' => 40,
        ];

        $this->client->request('POST', '/admin/order/update/' . $lastOrder->getId(), [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ], json_encode($originalOrder));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/admin/order/detail/' . $lastOrder->getId());
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $detailOrder = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(11, $detailOrder['products'][0]['quantity']);
        $this->assertEquals(40, $detailOrder['products'][1]['quantity']);
    }

}
