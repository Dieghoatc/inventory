<?php

namespace App\Services;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\ProductWarehouse;
use App\Entity\Warehouse;
use App\Repository\OrderProductRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductWarehouseRepository;
use App\Repository\WarehouseRepository;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use LogicException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
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

    public const PRODUCT_CODE = 0;

    public const PRODUCT_TITLE = 1;

    public const PRODUCT_DETAIL = 2;

    public const PRODUCT_QUANTITY = 3;

    public const PRODUCT_PRICE = 4;

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

    protected function getQueryFilter(array $rowData): array
    {
        $queryFilter = null;
        if (\array_key_exists('uuid', $rowData)) {
            $queryFilter = ['uuid' => $rowData['uuid']];
        }
        if (\array_key_exists('code', $rowData)) {
            $queryFilter = ['code' => $rowData['code']];
        }

        if (null === $queryFilter) {
            throw new InvalidArgumentException('No one query filter, Code or Uuid was defined.');
        }

        return $queryFilter;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public function processXls(array $data): void
    {
        if (!$data['products'] instanceof UploadedFile) {
            throw new InvalidArgumentException('The uploaded file class does not exits.');
        }
        $warehouse = $this->warehouseRepo->find($data['warehouse']);
        if (!$warehouse instanceof Warehouse) {
            throw new InvalidArgumentException('The Warehouse class does not exits.');
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
            if (0 === $key || '' === $item[self::PRODUCT_CODE] || null === $item[self::PRODUCT_CODE]
                || \in_array($item[self::PRODUCT_CODE], $productsAdded, true)) {
                continue;
            }

            $product = $this->productRepo->findOneBy(['code' => $item[self::PRODUCT_CODE]]);

            if (!$product instanceof Product) {
                $product = new Product();
            }

            $product->setStatus(Product::STATUS_ACTIVE);
            $product->setCode($item[self::PRODUCT_CODE]);
            $product->setTitle($item[self::PRODUCT_TITLE]);
            $product->setPrice((float) $item[self::PRODUCT_PRICE]);
            $product->setDetail($item[self::PRODUCT_DETAIL]);

            $productWarehouse = $this->productWarehouseRepo->findOneBy([
                'warehouse' => $warehouse, 'product' => $product,
            ]);

            if (!$productWarehouse instanceof ProductWarehouse) {
                $productWarehouse = new ProductWarehouse();
                $productWarehouse->setProduct($product);
                $productWarehouse->setStatus(ProductWarehouse::STATUS_CONFIRMED);
                $productWarehouse->addQuantity($item[self::PRODUCT_QUANTITY]);
                $productWarehouse->setWarehouse($warehouse);
            } else {
                $currentQuantity = $productWarehouse->getQuantity() + $item[self::PRODUCT_QUANTITY];
                $productWarehouse->setQuantity($currentQuantity);
            }

            $product->addProductWarehouse($productWarehouse);
            $errors = $this->validator->validate($product);
            if (0 !== \count($errors)) {
                $validations[] = $validations;
            } else {
                $productsAdded[] = $item[self::PRODUCT_CODE];
                $this->manager->persist($product);
                $this->manager->persist($productWarehouse);
            }
        }
        $this->manager->flush();
    }

    public function moveProducts(array $productsToMove, Warehouse $warehouseSource, Warehouse $warehouseDestination): void
    {
        foreach ($productsToMove as $productToMove) {
            $queryFilter = $this->getQueryFilter($productToMove);
            $product = $this->productRepo->findOneBy($queryFilter);

            if (!$product) {
                throw new InvalidArgumentException('Product was not found');
            }

            if ($warehouseSource->getId() === $warehouseDestination->getId()) {
                throw new LogicException('Source and destination warehouse cannot be the same.');
            }

            $productSource = $this->productWarehouseRepo->findOneBy([
                'warehouse' => $warehouseSource, 'product' => $product,
            ]);

            if (!$productSource instanceof ProductWarehouse) {
                throw new LogicException('Error trying to get the product warehouse.');
            }

            $productSource->subQuantity($productToMove['quantity']);
            $productDestination = $this->productWarehouseRepo->findOneBy([
                'warehouse' => $warehouseDestination, 'product' => $product, 'status' => 0,
            ]);

            if ($productDestination instanceof ProductWarehouse) {
                $productDestination->addQuantity($productToMove['quantity']);
            } else {
                $productDestination = new ProductWarehouse();
                $productDestination->setWarehouse($warehouseDestination);
                $productDestination->addQuantity($productToMove['quantity']);
                $productDestination->setProduct($product);
                $productDestination->setStatus(ProductWarehouse::STATUS_PENDING_TO_CONFIRM);
            }

            $this->manager->persist($productDestination);
            $this->manager->persist($productSource);
        }
        $this->manager->flush();
    }

    public function addProductsToInventory(array $newProducts, Warehouse $warehouse): void
    {
        foreach ($newProducts as $newProduct) {
            $queryFilter = $this->getQueryFilter($newProduct);
            $product = $this->productRepo->findOneBy($queryFilter);

            if (!$product instanceof Product) {
                continue;
            }

            $productDestination = $this->productWarehouseRepo
                ->findOneBy(['warehouse' => $warehouse, 'product' => $product]);

            if (!$productDestination instanceof ProductWarehouse) {
                $productDestination = new ProductWarehouse();
                $productDestination->setProduct($product);
                $productDestination->setWarehouse($warehouse);
                $productDestination->setStatus(ProductWarehouse::STATUS_CONFIRMED);
            }

            $productDestination->addQuantity($newProduct['quantity']);
            $this->manager->persist($productDestination);
        }
        $this->manager->flush();
    }

    public function removeProductsFromInventory(array $productsData, Warehouse $warehouse): void
    {
        foreach ($productsData as $productData) {
            $queryFilter = $this->getQueryFilter($productData);
            $product = $this->productRepo->findOneBy($queryFilter);

            if (!$product instanceof Product) {
                continue;
            }

            $productDestination = $this->productWarehouseRepo
                ->findOneBy(['warehouse' => $warehouse, 'product' => $product]);

            if (!$productDestination instanceof ProductWarehouse) {
                throw new LogicException('Error trying to get the product warehouse.');
            }

            if ($productDestination->getQuantity() < $productData['quantity']) {
                throw new LogicException('The quantity to delete must be equal or less than the stored one.');
            }

            if ($productDestination instanceof ProductWarehouse) {
                $productDestination->subQuantity($productData['quantity']);
                $this->manager->persist($productDestination);
            }
        }
        $this->manager->flush();
    }

    public function approveProducts(Warehouse $warehouse): void
    {
        $productsPendingToApprove = $this->productWarehouseRepo->findBy([
            'warehouse' => $warehouse,
            'status' => ProductWarehouse::STATUS_PENDING_TO_CONFIRM,
        ]);

        foreach ($productsPendingToApprove as $productWarehouse) {
            $productWarehouse->setStatus(ProductWarehouse::STATUS_CONFIRMED);
            $this->manager->persist($productWarehouse);
        }

        $this->manager->flush();
    }

    public function add(array $productData, Warehouse $warehouse = null): Product
    {
        if (!\array_key_exists('uuid', $productData) && !\array_key_exists('code', $productData)) {
            throw new InvalidArgumentException('Either UUID or code was not provided');
        }

        if (\array_key_exists('uuid', $productData)) {
            $product = $this->productRepo->findOneBy(['uuid' => $productData['uuid']]);
        } else {
            $product = $this->productRepo->findOneBy(['code' => $productData['code']]);
        }

        if (!$product instanceof Product) {
            $product = new Product();
            $product->setCode($productData['code']);
            $product->setTitle($productData['code']);
            $product->setPrice(0);
            $product->setStatus(Product::STATUS_ACTIVE);

            if ($warehouse) {
                $productWarehouse = new ProductWarehouse();
                $productWarehouse->setProduct($product);
                $productWarehouse->setStatus(ProductWarehouse::STATUS_CONFIRMED);
                $productWarehouse->addQuantity(0);
                $productWarehouse->setWarehouse($warehouse);
                $product->addProductWarehouse($productWarehouse);
                $this->manager->persist($productWarehouse);
            }

            $this->manager->persist($product);
        }

        return $product;
    }

    public function crossOrderAgainstInventory(
        Order $order,
        Warehouse $warehouse = null
    ): void {
        $products = [];

        foreach ($order->getOrderProducts() as $orderProduct) {
            if (!$orderProduct->getProduct() instanceof Product) {
                throw new LogicException('This product was not found');
            }

            /* @var $orderProduct OrderProduct */
            $products[] = [
              'uuid' => $orderProduct->getProduct()->getUuid(),
              'quantity' => $orderProduct->getQuantity(),
            ];
        }
        if (null === $warehouse) {
            $warehouse = $order->getWarehouse();
        }

        $this->removeProductsFromInventory($products, $warehouse);
    }
}
