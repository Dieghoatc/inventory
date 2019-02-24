<?php

namespace App\DataProviders;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Repository\OrderRepository;
use App\Repository\WarehouseRepository;
use App\Services\OrderService;
use Automattic\WooCommerce\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WooCommerceProvider
{

    /** @var Client */
    private $wooCommerce;
    /** @var OrderService */
    private $orderService;
    /** @var WarehouseRepository */
    private $warehouseRepo;
    /** @var OrderRepository */
    private $orderRepo;

    public function __construct(
        OrderService $orderService,
        WarehouseRepository $warehouseRepo,
        OrderRepository $orderRepo,
        ParameterBagInterface $params
    )
    {
        $wooCommerceConnectionConfig = $params->get('woo_commerce');


        if(!is_array($wooCommerceConnectionConfig)) {
            throw new \InvalidArgumentException('Woo commerce configuration was not found.');
        }

        $this->orderService = $orderService;
        $this->wooCommerce = new Client(
            $wooCommerceConnectionConfig['url'],
            $wooCommerceConnectionConfig['customer_key'],
            $wooCommerceConnectionConfig['customer_secret'],
            [
                'version' => 'wc/v3',
            ]
        );
        $this->warehouseRepo = $warehouseRepo;
        $this->orderRepo = $orderRepo;
    }

    protected function getOrders(): array
    {
        return $this->wooCommerce->get('orders', ['status' => 'completed', 'per_page' => 20]);
    }

    protected function getCustomer(int $id): ?array
    {
        if ($id !== 0) {
            if(!$this->wooCommerce instanceof Client) {
                throw new \InvalidArgumentException('WooCommerce Client not found.');
            }

            return $this->wooCommerce->get("customers/{$id}");
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

        if (!$warehouse instanceof Warehouse) {
            throw new \InvalidArgumentException('Warehouse was not found');
        }

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
