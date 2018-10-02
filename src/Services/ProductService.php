<?php

namespace App\Services;


use App\Entity\Product;
use App\Entity\Warehouse;
use App\Repository\ProductRepository;
use App\Repository\WarehouseRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{
    /** @var $productRepo ProductRepository */
    protected $productRepo;

    /** @var $warehouseRepo WarehouseRepository */
    protected $warehouseRepo;

    /** @var $manager ObjectManager */
    protected $manager;

    /** @var $validator ValidatorInterface */
    protected $validator;

    public function __construct(
        ProductRepository $productRepo,
        WarehouseRepository $warehouseRepo,
        ObjectManager $manager,
        ValidatorInterface $validator
    ) {
        $this->productRepo = $productRepo;
        $this->warehouseRepo = $warehouseRepo;
        $this->manager = $manager;
        $this->validator = $validator;
    }

    public function processXls(array $data): void
    {
        $reader = new Xlsx();
        if(!$data['products'] instanceof UploadedFile){
            throw new ClassNotFoundException('The uploaded file class does not exits.');
        }
        $warehouse = $this->warehouseRepo->find($data['warehouse']);
        if(!$warehouse instanceof Warehouse) {
            throw new ClassNotFoundException('The Warehouse class does not exits.');
        }
        $spreadsheet = $reader->load($data['products']->getPathname());
        $items = $spreadsheet->getActiveSheet()->toArray();
        $this->storeProducts($items, $warehouse);
    }

    public function storeProducts(array $items, Warehouse $warehouse): void
    {
        $validations = [];
        foreach ($items as $item) {
            $product = new Product();
            $product->setCode($item[0]);
            $product->setTitle($item[1]);
            $product->setQuantity(0);
            $product->setWarehouse($warehouse);
            $errors = $this->validator->validate($product);
            if(\count($errors) > 0){
                $validations[] = $validations;
            } else {
                $this->manager->persist($product);
            }
        }
        $this->manager->flush();
    }
}