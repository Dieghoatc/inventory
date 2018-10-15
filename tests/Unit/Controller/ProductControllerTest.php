<?php

namespace App\Tests\Unit\Controller;

use App\Tests\Unit\Utils\UserWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends UserWebTestCase
{
    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testOkByAllRoutes(string $httpMethod, string $url): void
    {
        $this->logIn();
        $this->client->request($httpMethod, $url);
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function getUrlsForRegularUsers():?\Generator
    {
        yield ['GET', '/admin/product/'];
        yield ['GET', '/admin/product/upload'];
        //yield ['GET', '/admin/product/template'];
        yield ['GET', '/admin/product/all/1'];
    }

}