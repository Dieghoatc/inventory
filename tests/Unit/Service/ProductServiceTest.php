<?php

namespace App\Tests\Unit\Service;

use App\Entity\Product;
use App\Entity\ProductWarehouse;
use App\Entity\Warehouse;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductServiceTest extends WebTestCase
{
    /** @var $client Client */
    public $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testStoreProducts(): void
    {
        //Warehouse taken from WarehouseFixtures.php
        /** @var $warehouse Warehouse */
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $products = [
            ['Code', 'Product Name', 'Quantity', 'Price'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-01', '100', '100'],
            ['CODE-TEST-02', 'PRODUCT-TEST-NAME-02', '100', '100'],
            ['CODE-TEST-03', 'PRODUCT-TEST-NAME-03', '100', '100'],
            ['CODE-TEST-04', 'PRODUCT-TEST-NAME-04', '100', '100'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-04', '100', '100'],
        ];
        $productService->storeProducts($products, $warehouse);
        $productsByWarehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(ProductWarehouse::class)->findBy(['warehouse' => $warehouse]);
        $this->assertCount(7, $productsByWarehouse);
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
            ['uuid' => $productToWork->getUuid(), 'quantity' => '40'],
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
            ['uuid' => $productToWork->getUuid(), 'quantity' => '10'],
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
            ['code' => 'CODE-TEST-01', 'quantity' => 10],
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
        $this->assertCount(7, $products);
    }

    public function testMoveProductsWarehouseDoesNotHaveTheProduct(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Usa']);

        /** @var $productToWork Product */
        $productToWork = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-02']);

        $dataPrepared = [
            ['code' => $productToWork->getCode(), 'quantity' => 10],
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

        $this->assertEquals(10, $product->getQuantity());
        $this->assertCount(2, $products);
    }

    public function testUpdateQuantityFromArray(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);

        $dataPrepared = [
            ['Code', 'Title', 'Quantity', 'Price'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-01', '40', '100'],
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

    public function testRemoveProductsFromInventory(): void
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
            ['code' => 'CODE-TEST-01', 'quantity' => 10],
        ];
        $productService->removeProductsFromInventory($dataPrepared, $warehouse);
        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouse, 'product' => $productToWork]);

        $products = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findBy(['warehouse' => $warehouse]);

        $this->assertEquals(90, $product->getQuantity());
        $this->assertCount(7, $products);
    }

    public function testRemoveProductsFromInventory0(): void
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
            ['code' => 'CODE-TEST-01', 'quantity' => 0],
        ];
        $productService->removeProductsFromInventory($dataPrepared, $warehouse);
        $product = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouse, 'product' => $productToWork]);

        $products = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findBy(['warehouse' => $warehouse]);

        $this->assertEquals(90, $product->getQuantity());
        $this->assertCount(7, $products);
    }

    public function testRemoveProductsFromInventoryTryingToDeleteGreaterValue(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);

        $dataPrepared = [
            ['code' => 'CODE-TEST-01', 'quantity' => 100],
        ];

        $this->expectException(\LogicException::class);
        $productService->removeProductsFromInventory($dataPrepared, $warehouse);
    }

    public function testMoveProductsFromAndBAndApproveThem(): void
    {
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);

        /** @var $productToWork Product */
        $productToWork = $this->client->getContainer()->get('doctrine')
            ->getRepository(Product::class)
            ->findOneBy(['code' => 'CODE-TEST-03']);

        /** @var $warehouseSource Warehouse */
        $warehouseSource = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        /** @var $warehouseDestination Warehouse */
        $warehouseDestination = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Usa']);

        $dataPrepared = [
            ['uuid' => $productToWork->getUuid(), 'quantity' => 50],
        ];

        $productService->moveProducts($dataPrepared, $warehouseSource, $warehouseDestination);

        /** Testing initial state of products before move them to approved */
        $productWarehouseSource = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouseSource, 'product' => $productToWork, 'status' => 1]);

        $this->assertEquals(50, $productWarehouseSource->getQuantity());

        $productWarehouseDestination = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouseDestination, 'product' => $productToWork]);

        $this->assertEquals(50, $productWarehouseDestination->getQuantity());

        /* Approving products in destination warehouse */
        $productService->approveProducts($warehouseDestination);

        /** Testing final state between on destination warehouse. */
        $productWarehouseDestination = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findBy(['warehouse' => $warehouseDestination, 'product' => $productToWork, 'status' => 0]);

        $this->assertCount(0, $productWarehouseDestination);

        $productWarehouseDestination = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(ProductWarehouse::class)
            ->findOneBy(['warehouse' => $warehouseDestination, 'product' => $productToWork, 'status' => 1]);

        $this->assertEquals(50, $productWarehouseDestination->getQuantity());
    }
}
