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

    public function setUp(): void
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
            ['Code', 'Product Name', 'Quantity', 'Price'],
            ['CODE-TEST-01', 'PRODUCT-TEST-NAME-01', '0', 100],
            ['CODE-TEST-02', 'PRODUCT-TEST-NAME-01', '0', 100],
            ['CODE-TEST-03', 'PRODUCT-TEST-NAME-01', '0', 100],
            ['CODE-TEST-04', 'PRODUCT-TEST-NAME-01', '0', 100],
        ];
        $productService->storeProducts($products, $warehouse);
        $products = $this->productRepo->findAll();
        $this->assertCount(7, $products);
    }

}
