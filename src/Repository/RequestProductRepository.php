<?php

namespace App\Repository;

use App\Entity\RequestProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RequestProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestProduct[]    findAll()
 * @method RequestProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RequestProduct::class);
    }

//    /**
//     * @return OrderProduct[] Returns an array of OrderProduct objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderProduct
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
