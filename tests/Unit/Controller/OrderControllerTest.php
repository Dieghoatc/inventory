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

        $this->assertEquals(4, $crawler->filter('.sidebar.navbar-nav > .nav-item')->count());
    }

}
