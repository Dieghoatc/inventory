<?php

namespace App\Tests\Unit\Service;


use App\Entity\Product;
use App\Entity\Warehouse;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductServiceTest extends WebTestCase
{
    public $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testStoreProducts(): void
    {
        //Warehouse taken from WarehouseFixtures.php
        $warehouse = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        $productService = $this->client->getContainer()->get(ProductService::class);
        $products = [
            ['Code', 'Product Name', 'Quantity'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-01', '100'],
            ['CODE-TEST-02', 'PRODUCT-TEST-NAME-02', '100'],
            ['CODE-TEST-03', 'PRODUCT-TEST-NAME-03', '100'],
            ['CODE-TEST-04', 'PRODUCT-TEST-NAME-04', '100'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-04', '100'],
        ];
        $productService->storeProducts($products, $warehouse);
        $productsByWarehouse = $this->client->getContainer()->get('doctrine')->getRepository(Product::class)->findBy(['warehouse' => $warehouse]);
        $this->assertCount(4, $productsByWarehouse);
    }

    public function testMoveProduct(): void
    {
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouseSource = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        $warehouseDestination = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Usa']);
        $this->assertNotNull($warehouseDestination);
        /** @var $product Product */
        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01', 'warehouse' => $warehouseSource]);
        $this->assertNotNull($product);
        $dataPrepared = [
            ['uuid' => $product->getUuid(), 'quantity' => '40']
        ];
        $productService->moveProducts($dataPrepared, $warehouseDestination);
        $productSource = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01', 'warehouse' => $warehouseSource]);
        $this->assertEquals(60, $productSource->getQuantity());
        $productDestination = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01', 'warehouse' => $warehouseDestination]);
        $this->assertEquals(40, $productDestination->getQuantity());
        // Validate the correct number of product in destination warehouses.
        $productsDestinationWarehouse = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findBy(['warehouse' => $warehouseDestination]);
        $this->assertCount(1, $productsDestinationWarehouse);
    }

    public function testCaseUpdateQuantityExisting(): void
    {
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouseSource = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        $warehouseDestination = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Usa']);

        $productSource = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01', 'warehouse' => $warehouseSource]);

        $dataPrepared = [
            ['uuid' => $productSource->getUuid(), 'quantity' => '10']
        ];
        $productService->moveProducts($dataPrepared, $warehouseDestination);

        $productSource = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01', 'warehouse' => $warehouseSource]);
        $productDestination = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01', 'warehouse' => $warehouseDestination]);

        $productsDestinationWarehouse = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findBy(['warehouse' => $warehouseDestination]);
        $this->assertCount(1, $productsDestinationWarehouse);
        $this->assertEquals(50, $productSource->getQuantity());
        $this->assertEquals(50, $productDestination->getQuantity());
    }

    public function testMoveProducts(): void
    {
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        $dataPrepared = [
            ['code' => 'CODE-TEST-01']
        ];
        $productService->addProductsToInventory($dataPrepared, $warehouse);
        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['warehouse' => $warehouse, 'code' => 'CODE-TEST-01']);
        $products = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findBy(['warehouse' => $warehouse]);

        $this->assertEquals(51, $product->getQuantity());
        $this->assertCount(4, $products);
    }

    public function testUpdateQuantityFromArray(): void
    {
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        $dataPrepared = [
            ['Code', 'Title', 'Quantity'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-01', '49'],
        ];

        $productService->storeProducts($dataPrepared, $warehouse);

        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['warehouse' => $warehouse, 'code' => 'CODE-TEST-01']);
        $this->assertEquals(100, $product->getQuantity());
    }

    public function testUpdateQuantityFromArrayCase0(): void
    {
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        $dataPrepared = [
            ['Code', 'Title', 'Quantity'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-01', '0'],
        ];

        $productService->storeProducts($dataPrepared, $warehouse);

        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['warehouse' => $warehouse, 'code' => 'CODE-TEST-01']);
        $this->assertEquals(100, $product->getQuantity());
    }
}
