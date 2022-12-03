<?php

namespace App\Repository;

use App\Entity\Attendance;
use App\Entity\ClassSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClassSession>
 *
 * @method ClassSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassSession[]    findAll()
 * @method ClassSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassSession::class);
    }

    public function save(ClassSession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClassSession $entity, bool $flush = false): void
    {
        $entityManager = $this->getEntityManager();

        $attendanceRepo = $entityManager->getRepository(Attendance::class);
        $attendances = $attendanceRepo->findBy(['classSessionId' => $entity->getId()]);
        foreach ($attendances as $attendance) {
            $attendanceRepo->remove($attendance);
        }

        $entityManager->remove($entity);
        if ($flush) {
            $entityManager->flush();
        }
    }

    //    /**
    //     * @return ClassSession[] Returns an array of ClassSession objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ClassSession
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
