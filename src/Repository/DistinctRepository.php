<?php

namespace App\Repository;

use App\Entity\Distinct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Distinct>
 *
 * @method Distinct|null find($id, $lockMode = null, $lockVersion = null)
 * @method Distinct|null findOneBy(array $criteria, array $orderBy = null)
 * @method Distinct[]    findAll()
 * @method Distinct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DistinctRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Distinct::class);
    }

//    /**
//     * @return Distinct[] Returns an array of Distinct objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Distinct
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
