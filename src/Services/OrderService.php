<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Repository\CustomerRepository;
use App\Repository\OrderProductRepository;
use App\Repository\ProductRepository;
use App\Repository\WarehouseRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class OrderService
{
    /** @var $objectManager ObjectManager */
    private $objectManager;

    /** @var $productRepo ProductRepository */
    private $productRepo;

    /** @var $customerRepo CustomerRepository */
    private $customerRepo;

    /** @var $orderProductRepo OrderProductRepository */
    private $orderProductRepo;

    /** @var $warehouseRepo WarehouseRepository */
    private $warehouseRepo;

    public function __construct(
        ObjectManager $objectManager,
        ProductRepository $productRepo,
        OrderProductRepository $orderProductRepo,
        CustomerRepository $customerRepo,
        WarehouseRepository $warehouseRepo
    ) {
        $this->objectManager = $objectManager;
        $this->productRepo = $productRepo;
        $this->orderProductRepo = $orderProductRepo;
        $this->warehouseRepo = $warehouseRepo;
        $this->customerRepo = $customerRepo;
    }

    public function addOrder(array $orderItem, User $user): array
    {
        $order = new Order();
        $customer = $this->customerRepo->find($orderItem['customer']['id']);
        if (!$customer instanceof Customer) {
            throw new \LogicException('Customer not found');
        }

        $warehouse = $this->warehouseRepo->find($orderItem['warehouse']['id']);
        if (!$warehouse instanceof Warehouse) {
            throw new \LogicException('Warehouse not found');
        }

        $order->setCustomer($customer);
        $order->setWarehouse($warehouse);
        $order->setCode($orderItem['code']);
        $order->setSource($orderItem['source']);
        $order->setStatus($orderItem['status']);
        $this->objectManager->persist($order);

        $products = $orderItem['products'];
        foreach ($products as $productItem) {
            $product = $this->productRepo->findOneBy(['code' => $productItem['code']]);

            if (!$product instanceof Product) {
                throw new \LogicException('The product does not exits');
            }
            $orderProduct = new OrderProduct();
            $orderProduct->setOrder($order);
            $orderProduct->setProduct($product);
            $orderProduct->setQuantity($productItem['quantity']);
            $this->objectManager->persist($orderProduct);
        }

        $comments = $orderItem['comments'];
        foreach ($comments as $commentItem) {
            $comment = new Comment();
            $comment->setOrder($order);
            $comment->setContent($commentItem['content']);
            $comment->setUser($user);
            $order->addComment($comment);
            $this->objectManager->persist($comment);
        }

        $this->objectManager->flush();
        return $this->getOrder($order);
    }

    public function getOrder(Order $order): array
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $data = $serializer->normalize($order, 'json', ['attributes' => [
            'id',
            'code',
            'createdAtAsIso8601',
            'status',
            'source',
            'warehouse' => [
                'id',
                'name',
            ],
            'customer' => ['email', 'firstName', 'lastName', 'defaultAddress' => [
                'address', 'zipCode', 'city' => [
                    'name',
                    'state' => [
                        'name',
                        'country' => [
                            'name',
                        ],
                    ],
                ],
            ]],
            'comments' => [
                'id',
                'content',
            ],
        ]]);

        return [
            'order' => $data,
            'products' => $this->orderProductRepo->allProductsByOrder($order),
        ];
    }
}
