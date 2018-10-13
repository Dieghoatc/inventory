<?php

namespace App\Services;


use App\Entity\Product;
use App\Entity\Warehouse;
use App\Repository\ProductRepository;
use App\Repository\WarehouseRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
        if(!$data['products'] instanceof UploadedFile){
            throw new ClassNotFoundException('The uploaded file class does not exits.');
        }
        $warehouse = $this->warehouseRepo->find($data['warehouse']);
        if(!$warehouse instanceof Warehouse) {
            throw new ClassNotFoundException('The Warehouse class does not exits.');
        }
        $spreadsheet = IOFactory::load($data['products']->getPathname());
        $items = $spreadsheet->getActiveSheet()->toArray();
        $this->storeProducts($items, $warehouse);
    }

    public function storeProducts(array $items, Warehouse $warehouse): void
    {
        $validations = [];
        foreach ($items as $key => $item) {
            if($key === 0){
                continue;
            }
            $product = new Product();
            $product->setCode($item[0]);
            $product->setTitle($item[1]);
            $product->setQuantity((int) $item[2]);
            $product->setWarehouse($warehouse);
            $errors = $this->validator->validate($product);
            if(\count($errors) !== 0){
                $validations[] = $validations;
            } else {
                $this->manager->persist($product);
            }
        }
        $this->manager->flush();
    }

    public function moveProduct(array $items, Warehouse $warehouse){
        foreach ($items as $item) {
            $product = $this->productRepo->findOneBy(['warehouse' => $warehouse, 'uuid' => $item['uuid']]);
            if(!$product){
                $product = new Product();
                $product->setCode($item['code']);
                $product->setQuantity($item['quantity']);
                $product->setWarehouse($warehouse);
            }
            $product->setQuantity($product->getQuantity() + $item['quantity']);
            $this->manager->persist($product);
        }
        $this->manager->flush();
    }
}