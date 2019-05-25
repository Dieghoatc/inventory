<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Services\OrderService;
use App\Services\ProductService;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class OrderListener
{
    /** @var EntityManagerInterface */
    private $manager;
    /** @var OrderService */
    private $productService;

    public function __construct(
        EntityManagerInterface $manager,
        ProductService $productService
    ) {
        $this->manager = $manager;
        $this->productService = $productService;
    }


    public function postPersist(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();

        if($order instanceof Order && $order->getParent() === null) {
            $this->addOrderStatus($order);
        }
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();
        if($order instanceof Order && $order->getParent() === null) {
            $this->addOrderStatus($order);

            if($order->getStatus() === Order::STATUS_SENT) {
                $this->orderSent($order);
            }

        }
    }

    protected function addOrderStatus(Order $order): void
    {
        $orderStatus = new OrderStatus();
        $orderStatus->setStatus($order->getStatus());
        $orderStatus->setOrder($order);
        $order->addOrderStatus($orderStatus);

        $this->manager->persist($orderStatus);
        $this->manager->flush();
    }

    protected function orderSent(Order $order): void
    {
        $this->productService->crossOrderAgainstInventory($order);
    }

}
