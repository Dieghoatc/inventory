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

    public function findByWarehouse(Warehouse $warehouse, int $status): array
    {
        $products =  $this->createQueryBuilder('pw')
            ->select('pw')
            ->addSelect('w')
            ->addSelect('p')
            ->innerJoin('pw.product', 'p')
            ->innerJoin('pw.warehouse', 'w')
            ->where('pw.warehouse = :warehouse')
            ->andWhere('pw.status = :status')
            ->setParameter('status', $status)
            ->setParameter('warehouse', $warehouse)
            ->orderBy('pw.product', 'ASC')
            ->getQuery()
            ->getArrayResult();

        foreach ($products as $key => $product){
            $products[$key]['uuid'] = $product['product']['uuid'];
            $products[$key]['title'] = $product['product']['title'];
            $products[$key]['code'] = $product['product']['code'];
            $products[$key]['price'] = $product['product']['price'];
        }

        return $products;
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
