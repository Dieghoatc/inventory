<?php

namespace App\Tests\Unit\Service;


use App\Entity\Product;
use App\Entity\ProductWarehouse;
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
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        /** @var $productService ProductService */
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
        $productsByWarehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(ProductWarehouse::class)->findBy(['warehouse' => $warehouse]);
        $this->assertCount(8, $productsByWarehouse);
    }

    public function testMoveProduct(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouseSource = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        $warehouseDestination = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Usa']);
        $this->assertNotNull($warehouseDestination);

        /** @var $productToWork Product */
        $productToWork = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01']);
        $this->assertNotNull($productToWork);

        $dataPrepared = [
            ['uuid' => $productToWork->getUuid(), 'quantity' => '40']
        ];
        $productService->moveProducts($dataPrepared, $warehouseSource, $warehouseDestination);

        $productWarehouseSource = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouseSource, 'product' => $productToWork]);
        $this->assertNotNull($productWarehouseSource);
        $this->assertEquals(60, $productWarehouseSource->getQuantity());

        $productWarehouseDestination = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouseDestination, 'product' => $productToWork]);
        $this->assertNotNull($productWarehouseDestination);
        $this->assertEquals(40, $productWarehouseDestination->getQuantity());
    }

    public function testCaseUpdateQuantityExisting(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouseSource = $this->client->getContainer()
            ->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);

        $warehouseDestination = $this->client->getContainer()
            ->get('doctrine')->getRepository(Warehouse::class)->findOneBy(['name' => 'Usa']);

        /** @var $productToWork Product */
        $productToWork = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01']);
        $this->assertNotNull($productToWork);

        $dataPrepared = [
            ['uuid' => $productToWork->getUuid(), 'quantity' => '10']
        ];

        $productService->moveProducts($dataPrepared, $warehouseSource, $warehouseDestination);

        $productSource = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['product' => $productToWork, 'warehouse' => $warehouseSource]);

        $productDestination = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['product' => $productToWork, 'warehouse' => $warehouseDestination]);

        $this->assertEquals(50, $productSource->getQuantity());
        $this->assertEquals(50, $productDestination->getQuantity());
    }

    public function testMoveProducts(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);

        /** @var $productToWork Product */
        $productToWork = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01']);

        $dataPrepared = [
            ['code' => 'CODE-TEST-01', 'quantity' => 10]
        ];
        $productService->addProductsToInventory($dataPrepared, $warehouse);
        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouse, 'product' => $productToWork]);

        $products = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findBy(['warehouse' => $warehouse]);

        $this->assertEquals(60, $product->getQuantity());
        $this->assertCount(8, $products);
    }

    public function testUpdateQuantityFromArray(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);

        $dataPrepared = [
            ['Code', 'Title', 'Quantity'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-01', '40'],
        ];

        $productService->storeProducts($dataPrepared, $warehouse);

        /** @var $productToWork Product */
        $productToWork = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01']);

        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouse, 'product' => $productToWork]);

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

        /** @var $productToWork Product */
        $productToWork = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-01']);

        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouse, 'product' => $productToWork]);

        $this->assertEquals(100, $product->getQuantity());
    }
}
