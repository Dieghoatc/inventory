<?php

namespace App\Tests\Unit\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WarehouseControllerTest extends WebTestCase
{
    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testOkByAllRoutes(string $httpMethod, string $url): void
    {
        $client = static::createClient();
        $client->request($httpMethod, $url);
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
    public function getUrlsForRegularUsers():?\Generator
    {
        yield ['GET', '/warehouse/'];
        yield ['GET', '/warehouse/edit/1'];
        yield ['GET', '/warehouse/all'];
    }

}