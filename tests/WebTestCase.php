<?php

namespace App\Tests;

use App\Entity\City;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Services\CustomerService;
use App\Services\OrderService;
use App\Services\ProductService;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    public function createProduct(
        Warehouse $warehouse = null,
        string $code = 'CODE-TEST-01',
        int $quantity = 100
    ): Product {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);

        if (null === $warehouse) {
            $warehouse = $this->client->getContainer()->get('doctrine')
                ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        }

        // Create product
        $products = [
            ['Code', 'Product Name', 'Description', 'Quantity', 'Price'],
            [$code, "NAME-{$code}", "DESCRIPTION-{$code}", $quantity, '100'],
        ];
        $productService->storeProducts($products, $warehouse);

        /** @var $product Product */
        $product = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => $code]);

        return $product;
    }

    public function getCustomerByEmail(string $email): ?Customer
    {
        return $this->client->getContainer()->get('doctrine')
            ->getRepository(Customer::class)->findOneBy(['email' => $email]);
    }

    public function getWarehouseByName(string $name): Warehouse
    {
        return $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => $name]);
    }

    public function getCityByName(string $name): City
    {
        return $this->client->getContainer()->get('doctrine')
            ->getRepository(City::class)->findOneBy(['name' => $name]);
    }

    public function getOrderById(int $id): Order
    {
        return $this->client->getContainer()->get('doctrine')
            ->getRepository(Order::class)->find($id);
    }

    public function createCustomer(array $data = []): Customer
    {
        $city = $this->getCityByName('West Palm Beach');
        $customerData = [
          'firstName' => 'TEST FIRST NAME',
          'lastName' => 'TEST LAST NAME',
          'phone' => '99999999',
          'email' => 'test@example.com',
          'addresses' => [
              [
                  'city' => [
                      'name' => $city->getName(),
                      'id' => $city->getId()
                  ],
                  'address' => 'TEST ADDRESS',
                  'zipCode' => '99999',
              ]
          ]
        ];

        $mergedCustomerData = array_replace($customerData, $data);
        /** @var $customerService CustomerService */
        $customerService = $this->client->getContainer()->get(CustomerService::class);
        return $customerService->addOrUpdate($mergedCustomerData);
    }

    public function getUserByEmail(string $email): User
    {
        return $this->client->getContainer()->get('doctrine')
            ->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function createOrder(array $data): Order
    {
        $warehouse = $this->getWarehouseByName('Colombia');
        $customer = $this->createCustomer();
        $productA = $this->createProduct($warehouse, 'TEST-CREATE-PRODUCT-A');
        $productB = $this->createProduct($warehouse, 'TEST-CREATE-PRODUCT-B');
        $orderData = [
            'code' => 'UNIT-TEST-CODE01',
            'comment' => 'EMPTY TEST ORDER COMMENT',
            'paymentMethod' => Order::PAYMENT_CREDIT_CARD,
            'status' => Order::STATUS_CREATED,
            'source' => Order::SOURCE_WEB,
            'warehouse' => [
                'id' => $warehouse->getId(),
            ],
            'customer' => [
                'id' => $customer->getId(),
            ],
            'products' => [
                [
                    'uuid' => $productA->getUuid(),
                    'quantity' => 10,
                ],
                [
                    'uuid' => $productB->getUuid(),
                    'quantity' => 20,
                ],
            ],
            'comments' => [
                [
                    'content' => 'PHP Unit test comment A.',
                ],
                [
                    'content' => 'PHP Unit test comment B.',
                ],
            ],
        ];

        $mergedOrderData = array_replace($orderData, $data);
        /** @var $orderService OrderService */
        $orderService = $this->client->getContainer()->get(OrderService::class);
        $user = $this->getUserByEmail('sbarbosa115@gmail.com');
        $orderCreated = $orderService->add($mergedOrderData, $user);
        return $this->getOrderById($orderCreated['order']['id']);
    }
}
