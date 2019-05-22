<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Warehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return Product[]
     */
    public function findAllAsArray(Warehouse $warehouse): array
    {
        return $this->createQueryBuilder('p')
            ->select('p,w')
            ->innerJoin('p.warehouse', 'w')
            ->where('p.warehouse = :warehouse')
            ->setParameter('warehouse', $warehouse)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    public function findByUuids(array $uuids): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.uuid in (:uuids)')
            ->setParameter('uuids', implode(',', $uuids))
            ->getQuery()
            ->getResult();
    }

}
