<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Entity\OrderStatus;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class OrderListener
{

    /** @var EntityManagerInterface */
    protected $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }


    public function postPersist(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();

        if($order instanceof Order) {
            $this->addOrderStatus($order);
        }
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();
        if($order instanceof Order) {
            $this->addOrderStatus($order);
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

}
