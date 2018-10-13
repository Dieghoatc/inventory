<?php

namespace App\Tests\Unit\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
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
        yield ['GET', '/product/'];
        yield ['GET', '/product/upload'];
        //yield ['GET', '/product/template'];
        yield ['GET', '/product/all/1'];
    }

}