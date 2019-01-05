<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderProduct[]    findAll()
 * @method OrderProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderProduct::class);
    }

    public function allProductsByOrder(Order $order): array
    {
        $products = $this->createQueryBuilder('op ')
            ->innerJoin('op.order', 'o')
            ->where('o.id = :order')
            ->setParameter('order', $order->getId())
            ->getDQL();

        return $products;
    }
}
