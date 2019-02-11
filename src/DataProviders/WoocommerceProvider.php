<?php

namespace App\DataProviders;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\WarehouseRepository;
use App\Services\OrderService;
use Automattic\WooCommerce\Client;

class WoocommerceProvider
{

    /** @var Client */
    private $woocomerce;
    /** @var OrderService */
    private $orderService;
    /** @var WarehouseRepository */
    private $warehouseRepo;
    /** @var OrderRepository */
    private $orderRepo;

    public function __construct(
        OrderService $orderService,
        WarehouseRepository $warehouseRepo,
        OrderRepository $orderRepo
    )
    {
        $this->orderService = $orderService;
        $this->woocomerce = new Client(
            'http://www.klassicfab.com/',
            'ck_7947cf0291280c8a0f53534e51f7e6d8b6f98689',
            'cs_84b8a40f572a92ceb37398ea5559f26b1c3baf6d',
            [
                'version' => 'wc/v3',
            ]
        );
        $this->warehouseRepo = $warehouseRepo;
        $this->orderRepo = $orderRepo;
    }

    protected function getOrders(): array
    {
        return $this->woocomerce->get('orders');
    }

    protected function getCustomer(int $id): ?object
    {
        if ($id !== 0) {
            return $this->woocomerce->get("customers/{$id}");
        }
        return null;
    }

    protected function transformProducts(array $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'code' => $product->sku,
                'quantity' => $product->quantity,
            ];
        }
        return $result;
    }

    protected function transformOrder(object $order): array
    {
        return [
            'customer' => [
                'email' => $order->billing->email,
                'firstName' => $order->billing->first_name,
                'lastName' => $order->billing->last_name,
                'phone' => $order->billing->phone,
                'addresses' => [
                    [
                        'address' => $order->billing->address_1,
                        'zipCode' => $order->billing->postcode,
                        'city' => [
                            'name' => $order->billing->city,
                            'state' => [
                                'name' => $order->billing->state,
                                'country' => [
                                    'name' => $order->billing->country
                                ]
                            ]
                        ],
                    ],
                ],
            ],
            'source' => Order::SOURCE_WEB,
            'paymentMethod' => Order::PAYMENT_CREDIT_CARD,
            'status' => Order::STATUS_CREATED,
            'code' => $order->id,
            'comment' => '',
            'products' => $this->transformProducts($order->line_items),
        ];
    }

    public function syncOrders(User $user): void
    {
        $orders = $this->getOrders();
        $warehouse = $this->warehouseRepo->findOneBy(['name' => 'Colombia']);
        foreach ($orders as $order) {
            $orderExist = $this->orderRepo->findOneBy(['code' => $order->id]);
            if ($orderExist instanceof Order) {
                continue;
            }

            $orderTransformed = $this->transformOrder($order);
            $orderTransformed['warehouse'] = [
                'name' => $warehouse->getName(),
                'id' => $warehouse->getId()
            ];
            $this->orderService->add($orderTransformed, $user);
        }
    }

}
