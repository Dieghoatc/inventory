<?php

namespace App\Services;


use App\Entity\Product;
use App\Reader\ProductFileReader;
use App\Repository\ProductRepository;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductService
{
    /** @var $productRepo ProductRepository */
    protected $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function processXls(UploadedFile $uploadedFile): void
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($uploadedFile->getPathname());
        $items = $spreadsheet->getActiveSheet()->toArray();
        $this->storeProductsFromArray($items);
    }

    public function storeProductsFromArray(array $items): void
    {
        foreach ($items as $item) {
            $product = new Product();
            $product->setCode();

        }
    }
}