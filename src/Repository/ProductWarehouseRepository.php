<?php

namespace App\Repository;

use App\Entity\ProductWarehouse;
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

//    /**
//     * @return ProductWarehouse[] Returns an array of ProductWarehouse objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

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
