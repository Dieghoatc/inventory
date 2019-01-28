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

    /** @var $logService LogService */
    protected $logService;

    public function __construct(
        ProductRepository $productRepo,
        OrderProductRepository $orderProductRepo,
        ProductWarehouseRepository $productWarehouseRepo,
        WarehouseRepository $warehouseRepo,
        ObjectManager $manager,
        ValidatorInterface $validator,
        LogService $logService
    ) {
        $this->orderProductRepo = $orderProductRepo;
        $this->productRepo = $productRepo;
        $this->productWarehouseRepo = $productWarehouseRepo;
        $this->warehouseRepo = $warehouseRepo;
        $this->manager = $manager;
        $this->validator = $validator;
        $this->logService = $logService;
    }

    public function processXls(array $data): void
    {
        if (!$data['products'] instanceof UploadedFile) {
            throw new ClassNotFoundException('The uploaded file class does not exits.');
        }
        $warehouse = $this->warehouseRepo->find($data['warehouse']);
        if (!$warehouse instanceof Warehouse) {
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
            if (0 === $key || '' === $item[0] || null === $item[0] || 0 === $item[2] || \in_array($item[0], $productsAdded, true)) {
                continue;
            }

            $product = $this->productRepo->findOneBy(['code' => $item[0]]);

            if (!$product instanceof Product) {
                $product = new Product();
                $product->setCode($item[0]);
                $product->setTitle($item[1]);
                $product->setPrice((float) $item[3]);
            }

            $productWarehouse = $this->productWarehouseRepo->findOneBy([
                'warehouse' => $warehouse, 'product' => $product,
            ]);

            if (!$productWarehouse instanceof ProductWarehouse) {
                $productWarehouse = new ProductWarehouse();
                $productWarehouse->setProduct($product);
                $productWarehouse->setStatus(1);
                $productWarehouse->addQuantity($item[2]);
                $productWarehouse->setWarehouse($warehouse);
            } else {
                $currentQuantity = $productWarehouse->getQuantity() + $item[2];
                $productWarehouse->setQuantity($currentQuantity);
            }

            $product->addProductWarehouse($productWarehouse);
            $errors = $this->validator->validate($product);
            if (0 !== \count($errors)) {
                $validations[] = $validations;
            } else {
                $productsAdded[] = $item[0];
                $this->manager->persist($product);
                $this->manager->persist($productWarehouse);
            }
        }
        $this->manager->flush();
    }

    public function moveProducts(array $items, Warehouse $warehouseSource, Warehouse $warehouseDestination): void
    {
        foreach ($items as $item) {
            $product = $this->productRepo->findOneBy(['uuid' => $item['uuid']]);
            if (!$product) {
                throw new \InvalidArgumentException('Product was not found');
            }

            if ($warehouseSource->getId() === $warehouseDestination->getId()) {
                throw new \LogicException('Source and destination warehouse cannot be the same.');
            }

            $productSource = $this->productWarehouseRepo->findOneBy([
                'warehouse' => $warehouseSource, 'product' => $product,
            ]);

            $productSource->subQuantity($item['quantity']);
            $productDestination = $this->productWarehouseRepo->findOneBy([
                'warehouse' => $warehouseDestination, 'product' => $product, 'status' => 0,
            ]);

            if ($productDestination instanceof ProductWarehouse) {
                $productDestination->addQuantity($item['quantity']);
            } else {
                $productDestination = new ProductWarehouse();
                $productDestination->setWarehouse($warehouseDestination);
                $productDestination->addQuantity($item['quantity']);
                $productDestination->setProduct($product);
                $productDestination->setStatus(0);
            }

            $this->manager->persist($productDestination);
            $this->manager->persist($productSource);
        }
        $this->manager->flush();
    }

    public function addProductsToInventory(array $items, Warehouse $warehouse): void
    {
        foreach ($items as $item) {
            $product = $this->productRepo->findOneBy(['code' => $item['code']]);

            if (!$product instanceof Product) {
                continue;
            }

            $productDestination = $this->productWarehouseRepo
                ->findOneBy(['warehouse' => $warehouse, 'product' => $product]);

            if (!$productDestination instanceof ProductWarehouse) {
                $productDestination = new ProductWarehouse();
                $productDestination->setProduct($product);
                $productDestination->setWarehouse($warehouse);
                $productDestination->setStatus(1);
            }

            $productDestination->addQuantity($item['quantity']);
            $this->manager->persist($productDestination);
        }
        $this->manager->flush();
    }

    public function removeProductsFromInventory(array $items, Warehouse $warehouse): void
    {
        foreach ($items as $item) {
            $product = $this->productRepo->findOneBy(['code' => $item['code']]);

            if (!$product instanceof Product) {
                continue;
            }

            $productDestination = $this->productWarehouseRepo
                ->findOneBy(['warehouse' => $warehouse, 'product' => $product]);

            if ($productDestination->getQuantity() < $item['quantity']) {
                throw new \LogicException('The quantity to delete must be equal or less than the stored one.');
            }

            if ($productDestination instanceof ProductWarehouse) {
                $productDestination->subQuantity($item['quantity']);
                $this->manager->persist($productDestination);
            }
        }
        $this->manager->flush();
    }

    public function approveProducts(Warehouse $warehouse): void
    {
        $productsPendingToApprove = $this->productWarehouseRepo->findBy([
            'warehouse' => $warehouse,
            'status' => 0,
        ]);

        $products = [];
        foreach ($productsPendingToApprove as $product) {
            $products[] = ['code' => $product->getProduct()->getCode(), 'quantity' => $product->getQuantity()];
            $this->manager->remove($product);
        }

        $this->manager->flush();
        $this->addProductsToInventory($products, $warehouse);
    }
}
