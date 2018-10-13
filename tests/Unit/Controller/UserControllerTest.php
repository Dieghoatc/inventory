<?php

namespace App\Tests\Unit\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
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
        yield ['GET', '/user/'];
        yield ['GET', '/user/new'];
    }

}
