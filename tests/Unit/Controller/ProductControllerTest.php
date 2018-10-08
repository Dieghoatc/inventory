<?php

namespace App\Tests\Unit\Controller;

use App\Tests\Unit\DataFixtures\DataFixtureTestCase;

class ProductControllerTest extends DataFixtureTestCase
{

    public function testIndex(): void
    {
        $this->client->request('GET', '/product/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUpload(): void
    {
        $this->client->request('GET', '/product/upload');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAll(): void
    {
        $this->client->request('GET', '/product/all/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson(200, $this->client->getResponse()->getContent());

    }

}