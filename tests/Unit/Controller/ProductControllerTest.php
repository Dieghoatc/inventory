<?php

namespace App\Tests\Unit\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = self::createClient();
        $client->request('GET', '/product/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}