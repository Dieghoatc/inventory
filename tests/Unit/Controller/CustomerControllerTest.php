<?php

namespace App\Tests\Unit\Controller;

use App\Services\CustomerService;
use App\Services\ProductService;
use App\Tests\Unit\Utils\UserWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CustomerControllerTest extends UserWebTestCase
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
        $this->logIn();
        $this->client->request($httpMethod, $url);
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function getUrlsForRoleUpdate(): ?\Generator
    {
        yield ['GET', '/admin/customer/'];
    }

    private function createAddress(array $addressData = []): array
    {
        return array_merge_recursive([
            'zipCode' => 99999,
            'address' => 'ADDRESS NAME ST 999 AV',
            'city' => [
                //Taken from fixtures
                'id' => 1
            ]
        ], $addressData);
    }

    public function testUpdateCustomerAddresses(): void
    {
        $DEFAULT_CUSTOMER = 1;
        $this->logIn();
        /** @var $customerService CustomerService */
        $customerService = $this->client->getContainer()->get(CustomerService::class);

        $customer = $customerService->getCustomerById($DEFAULT_CUSTOMER);
        $customerAsArray = $customerService->getCustomerAsArray($customer);
        $customerAsArray['addresses'][] = $this->createAddress();
        $this->client->request('post',  "/admin/customer/update/{$DEFAULT_CUSTOMER}", [], [], [], json_encode($customerAsArray));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $customer = $customerService->getCustomerById($DEFAULT_CUSTOMER);
        $this->assertEquals(2, $customer->getAddresses()->count());
    }

}
