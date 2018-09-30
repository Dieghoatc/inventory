<?php

namespace App\Tests\Unit\Controller;

use App\Tests\Unit\DataFixtures\DataFixtureTestCase;

class WarehouseControllerTest extends DataFixtureTestCase
{

    protected $client;

    public function testIndex(): void
    {
        $this->client->request('GET', '/warehouse/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $this->client->request('GET', '/warehouse/edit/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}