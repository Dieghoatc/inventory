<?php

namespace App\Repository;

use App\Entity\ProductWarehouse;
use App\Entity\Warehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductWarehouse|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductWarehouse|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductWarehouse[]    findAll()
 * @method ProductWarehouse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductWarehouseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductWarehouse::class);
    }

    public function findByWarehouse(Warehouse $warehouse)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.warehouse = :warehouse')
            ->setParameter('warehouse', $warehouse)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /*
    public function findOneBySomeField($value): ?ProductWarehouse
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
