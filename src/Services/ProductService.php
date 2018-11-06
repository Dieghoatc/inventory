<?php

namespace App\Services;


use App\Entity\Product;
use App\Entity\ProductWarehouse;
use App\Entity\Warehouse;
use App\Repository\OrderProductRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductWarehouseRepository;
use App\Repository\WarehouseRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{
    /** @var $productRepo ProductRepository */
    protected $productRepo;

    /** @var $orderProductRepo OrderProductRepository */
    protected $orderProductRepo;

    /** @var $productWarehouseRepo ProductWarehouseRepository */
    protected $productWarehouseRepo;

    /** @var $warehouseRepo WarehouseRepository */
    protected $warehouseRepo;

    /** @var $manager ObjectManager */
    protected $manager;

    /** @var $validator ValidatorInterface */
    protected $validator;

    public function __construct(
        ProductRepository $productRepo,
        OrderProductRepository $orderProductRepo,
        ProductWarehouseRepository $productWarehouseRepo,
        WarehouseRepository $warehouseRepo,
        ObjectManager $manager,
        ValidatorInterface $validator
    ) {
        $this->orderProductRepo = $orderProductRepo;
        $this->productRepo = $productRepo;
        $this->productWarehouseRepo = $productWarehouseRepo;
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
        $productsAdded = [];
        foreach ($items as $key => $item) {
            if($key === 0 || $item[2] === 0 || \in_array($item[0], $productsAdded, true)){
                continue;
            }

            $product =  $this->productRepo->findOneBy(['code' => $item[0]]);

            if(!$product instanceof Product){
                $product = new Product();
                $product->setCode($item[0]);
                $product->setTitle($item[1]);
            }

            $productWarehouse = $this->productWarehouseRepo->findOneBy([
                'warehouse' => $warehouse, 'product' => $product, 'warehouse' => $warehouse
            ]);

            if(!$productWarehouse instanceof ProductWarehouse){
                $productWarehouse = new ProductWarehouse();
                $productWarehouse->setProduct($product);
                $productWarehouse->setStatus(1);
                $productWarehouse->setQuantity($item[2]);
                $productWarehouse->setWarehouse($warehouse);
            } else {
                $currentQuantity = $productWarehouse->getQuantity() + $item[2];
                $productWarehouse->setQuantity($currentQuantity);
            }

            $product->addProductWarehouse($productWarehouse);
            $errors = $this->validator->validate($product);
            if(\count($errors) !== 0){
                $validations[] = $validations;
            } else {
                $productsAdded[] = $item[0];
                $this->manager->persist($product);
                $this->manager->persist($productWarehouse);
            }
        }
        $this->manager->flush();
    }

    public function moveProducts(array $items, Warehouse $warehouse): void
    {
        foreach ($items as $item) {
            $productSource = $this->productRepo->findOneBy(['uuid' => $item['uuid']]);
            if(!$productSource){
                throw new \InvalidArgumentException ('Product was not found');
            }

            if($productSource->getQuantity() < $item['quantity']){
                throw new \InvalidArgumentException ('The quantity given is greater that product quantity.');
            }

            $productDestination =  $this->productRepo->findOneBy(['warehouse' => $warehouse, 'code' => $productSource->getCode()]);
            $quantitySource = $productSource->getQuantity() - $item['quantity'];
            if($productDestination instanceof Product) {
                $productDestination->addQuantity($item['quantity']);
            } else {
                $productDestination = new Product();
                $productDestination->setCode($productSource->getCode());
                $productDestination->setTitle($productSource->getTitle());
                $productDestination->setWarehouse($warehouse);
                $productDestination->setQuantity($item['quantity']);
            }

            if($productSource->getUuid() === $productDestination->getUuid()){
                throw new \LogicException('Source and destination warehouse cannot be the same.');
            }

            $productSource->setQuantity($quantitySource);
            $this->manager->persist($productDestination);
            $this->manager->persist($productSource);
        }
        $this->manager->flush();
    }

    public function addProductsToInventory(array $items, Warehouse $warehouse): void
    {
        foreach ($items as $item){
            $product = $this->productRepo->findOneBy(['code' => $item['code'], 'warehouse' => $warehouse]);
            if($product instanceof Product){
                $product->addQuantity(1);
                $this->manager->persist($product);
            }
        }
        $this->manager->flush();
    }
}