<?php
/**
 * Created by PhpStorm.
 * User: sbarbosa115
 * Date: 9/11/18
 * Time: 10:13 AM
 */

namespace App\Tests\Unit\Repository;

use App\Entity\ProductWarehouse;
use App\Entity\Warehouse;
use App\Repository\ProductRepository;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductRepositoryTest extends WebTestCase
{
    public $client;

    /** @var $productRepository ProductRepository */
    public $productRepo;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->productRepo = $this->client->getContainer()->get('doctrine')
            ->getRepository(ProductWarehouse::class);
    }

    public function testFindAllAsArray(): void
    {
        $warehouse = $this->client->getContainer()->get('doctrine')
            ->getRepository(Warehouse::class)->findOneBy(['name' => 'Colombia']);
        /** @var $productService ProductService */
        $productService = $this->client->getContainer()->get(ProductService::class);
        $products = [
            ['Code', 'Product Name', 'Quantity'],
            ['CODE-TEST-REPO-001', 'PRODUCT-TEST-NAME-01', '100'],
            ['CODE-TEST-REPO-002', 'PRODUCT-TEST-NAME-01', '50'],
            ['CODE-TEST-REPO-003', 'PRODUCT-TEST-NAME-01', '25'],
            ['CODE-TEST-REPO-004', 'PRODUCT-TEST-NAME-01', '12'],
        ];
        $productService->storeProducts($products, $warehouse);
        $products = $this->productRepo->findAllAsArray();
        $this->assertCount(4, $products);
    }

}