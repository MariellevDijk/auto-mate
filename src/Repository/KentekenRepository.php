<?php

namespace App\Repository;

use App\Entity\Kenteken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Kenteken|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kenteken|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kenteken[]    findAll()
 * @method Kenteken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KentekenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kenteken::class);
    }

    public function save($kenteken) {
        $this->getEntityManager()->persist($kenteken);
        $this->getEntityManager()->flush();
    }

    // /**
    //  * @return Kenteken[] Returns an array of Kenteken objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Kenteken
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
