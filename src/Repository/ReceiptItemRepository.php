<?php

namespace App\Repository;

use App\Entity\ReceiptItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReceiptItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReceiptItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReceiptItem[]    findAll()
 * @method ReceiptItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReceiptItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReceiptItem::class);
    }

    // /**
    //  * @return ReceiptItem[] Returns an array of ReceiptItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReceiptItem
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
