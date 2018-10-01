<?php

namespace App\Tests\Unit\Controller;

use App\Tests\Unit\DataFixtures\DataFixtureTestCase;

class UserControllerTest extends DataFixtureTestCase
{

    public function testIndex(): void
    {
        $this->client->request('GET', '/user/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testNew(): void
    {
        $this->client->request('GET', '/user/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCreate(): void
    {
        $this->client->request('POST', '/user/create');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}