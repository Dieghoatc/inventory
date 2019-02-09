<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
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

    /** @var $orderProductRepo OrderProductRepository */
    private $orderProductRepo;

    /** @var $warehouseRepo WarehouseRepository */
    private $warehouseRepo;

    /** @var $customerService CustomerService */
    private $customerService;

    public function __construct(
        ObjectManager $objectManager,
        ProductRepository $productRepo,
        OrderProductRepository $orderProductRepo,
        WarehouseRepository $warehouseRepo,
        CustomerService $customerService
    ) {
        $this->objectManager = $objectManager;
        $this->productRepo = $productRepo;
        $this->orderProductRepo = $orderProductRepo;
        $this->warehouseRepo = $warehouseRepo;
        $this->customerService = $customerService;
    }

    public function add(array $orderData, User $user): array
    {
        $order = new Order();
        $customer = $this->customerService->addOrUpdate($orderData['customer']);

        $warehouse = $this->warehouseRepo->find($orderData['warehouse']['id']);
        if (!$warehouse instanceof Warehouse) {
            throw new \LogicException('Warehouse not found');
        }

        $order->setCustomer($customer);
        $order->setWarehouse($warehouse);
        $order->setCode($orderData['code']);
        $order->setSource($orderData['source']);
        $order->setStatus($orderData['status']);
        $order->setComment($orderData['comment']);
        $order->setPaymentMetod($orderData['paymentMethod']);
        $this->objectManager->persist($order);

        if (!array_key_exists('products', $orderData)) {
            throw new \LogicException('The order must have products.');
        }

        $this->attachProducts($order, $orderData['products']);

        if (array_key_exists('comments', $orderData)) {
            $this->attachComments($order, $user, $orderData['comments']);
        }

        $this->objectManager->flush();

        return $this->getOrder($order);
    }

    public function attachProducts(Order $order, array $products): void
    {
        foreach ($products as $productItem) {
            $product = $this->productRepo->findOneBy(['uuid' => $productItem['uuid']]);

            if (!$product instanceof Product) {
                throw new \LogicException('The product does not exits');
            }
            $orderProduct = new OrderProduct();
            $orderProduct->setOrder($order);
            $orderProduct->setProduct($product);
            $orderProduct->setQuantity($productItem['quantity']);

            $this->objectManager->persist($orderProduct);
        }
    }

    public function attachComments(Order $order, User $user, array $comments): void
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
