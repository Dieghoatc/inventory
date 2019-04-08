<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Repository\WarehouseRepository;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class OrderService
{
    /** @var ObjectManager */
    private $objectManager;
    /** @var WarehouseRepository */
    private $warehouseRepo;
    /** @var CustomerService */
    private $customerService;
    /** @var ProductService */
    private $productService;

    public function __construct(
        ObjectManager $objectManager,
        WarehouseRepository $warehouseRepo,
        CustomerService $customerService,
        ProductService $productService
    ) {
        $this->objectManager = $objectManager;
        $this->warehouseRepo = $warehouseRepo;
        $this->customerService = $customerService;
        $this->productService = $productService;
    }

    private function setCommonOrderFields(Order $order, array $orderData): void
    {
        $customer = $this->customerService->addOrUpdate($orderData['customer']);

        $warehouse = $this->warehouseRepo->find($orderData['warehouse']['id']);
        if (!$warehouse instanceof Warehouse) {
            throw new LogicException('Warehouse not found');
        }

        $order->setCustomer($customer);
        $order->setWarehouse($warehouse);
        $order->setCode($orderData['code']);
        $order->setSource($orderData['source']);
        $order->setStatus($orderData['status']);
        $order->setComment($orderData['comment']);
        $order->setPaymentMethod($orderData['paymentMethod']);
        $this->objectManager->persist($order);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function syncProducts(Order $order, array $orderProductsData): void
    {
        /** @var $orderProduct OrderProduct */
        foreach ($order->getProducts() as $orderProduct) {
            $someFound = array_filter($orderProductsData, static function($productData) use ($orderProduct) {
                return $productData['uuid'] === $orderProduct->getUuid();
            });

            if (count($someFound) === 0) {
               $order->removeOrderProduct($orderProduct);
            }
        }

        foreach ($orderProductsData as $productData) {
            $product = $this->productService->add($productData);

            if(!$product instanceof Product) {
                throw new InvalidArgumentException('This product does not exist.');
            }

            if (!$order->isProductInOrder($product)) {
                $orderProduct = new OrderProduct();
                $orderProduct->setOrder($order);
                $orderProduct->setProduct($product);
                $orderProduct->setQuantity($productData['quantity']);
                $order->addOrderProduct($orderProduct);
            } else {
                $orderProduct->setQuantity($productData['quantity']);
            }
            $this->objectManager->persist($orderProduct);
        }

        $this->objectManager->flush();
    }

    private function attachComments(Order $order, User $user, array $comments): void
    {
        foreach ($comments as $commentItem) {
            $comment = new Comment();
            $comment->setContent($commentItem['content']);
            $comment->setUser($user);
            $comment->setOrder($order);
            $order->addComment($comment);
            $this->objectManager->persist($comment);
            $this->objectManager->persist($order);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function getOrderAsArray(Order $order): array
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        return $serializer->normalize($order, 'array', ['attributes' => [
            'id',
            'code',
            'status',
            'source',
            'createdAtAsString',
            'paymentMethod',
            'comment',
            'warehouse' => ['id', 'name'],
            'customer' => ['email', 'firstName', 'lastName', 'phone', 'addresses' => [
                'address', 'zipCode', 'city' => [
                    'name',
                    'state' => [
                        'name',
                        'country' => ['name'],
                    ],
                ],
            ]],
            'comments' => ['id', 'content'],
            'products' =>['quantity', 'uuid', 'product' => [
                'title', 'detail', 'code'
            ]],
        ]]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function add(array $orderData, User $user): array
    {
        $order = new Order();
        $this->setCommonOrderFields($order, $orderData);

        if (!array_key_exists('products', $orderData)) {
            throw new LogicException('The order must have products.');
        }

        $this->syncProducts($order, $orderData['products']);

        if (array_key_exists('comments', $orderData)) {
            $this->attachComments($order, $user, $orderData['comments']);
        }

        $this->objectManager->flush();
        return $this->getOrderAsArray($order);
    }

    /**
     * @throws ExceptionInterface
     */
    public function update(Order $order, array $newOrderData): array
    {
        $this->setCommonOrderFields($order, $newOrderData);
        $this->syncProducts($order, $newOrderData['products']);

        $this->objectManager->persist($order);
        $this->objectManager->flush();
        return $this->getOrderAsArray($order);
    }

    public function deleteOrder(Order $order): void
    {
        foreach ($order->getOrderProducts() as $orderProduct) {
            $this->objectManager->remove($orderProduct);
        }

        foreach ($order->getComments() as $comment) {
            $this->objectManager->remove($comment);
        }

        $this->objectManager->remove($order);
        $this->objectManager->flush();
    }
}
