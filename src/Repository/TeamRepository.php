<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\Leave;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }
    public function findLeavesByTeamOrderedByRequestDate(Team $team)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->from(Leave::class, 'l')
            ->join('l.user', 'u')
            ->where('u.team = :team')
            ->setParameter('team', $team)
            ->orderBy('l.requestDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Team[] Returns an array of Team objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Team
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
