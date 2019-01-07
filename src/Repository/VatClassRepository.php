<?php

namespace App\Repository;

use App\Entity\VatClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VatClass|null find($id, $lockMode = null, $lockVersion = null)
 * @method VatClass|null findOneBy(array $criteria, array $orderBy = null)
 * @method VatClass[]    findAll()
 * @method VatClass[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VatClassRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VatClass::class);
    }

    // /**
    //  * @return VatClass[] Returns an array of VatClass objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VatClass
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
