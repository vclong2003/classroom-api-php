<?php

namespace App\Repository;

use ApiPlatform\Metadata\Post;
use App\Entity\Classroom;
use App\Entity\ClassSession;
use App\Entity\Posts;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Classroom>
 *
 * @method Classroom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Classroom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Classroom[]    findAll()
 * @method Classroom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassroomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classroom::class);
    }

    public function save(Classroom $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Classroom $entity, bool $flush = false): void
    {
        $entityManager = $this->getEntityManager();

        $postRepo = $entityManager->getRepository(Posts::class);
        $posts = $postRepo->findBy(['classId' => $entity->getId()]);
        foreach ($posts as $post) {
            $postRepo->remove($post);
        }

        $classSessionRepo = $entityManager->getRepository(ClassSession::class);
        $classSessions = $classSessionRepo->findBy(['classId' => $entity->getId()]);
        foreach ($classSessions as $classSession) {
            $classSessionRepo->remove($classSession);
        }

        $studentRepo = $entityManager->getRepository(Student::class);
        $students = $studentRepo->findBy(['classId' => $entity->getId()]);
        foreach ($students as $student) {
            $studentRepo->remove($student);
        }

        $entityManager->remove($entity);
        if ($flush) {
            $entityManager->flush();
        }
    }

    //    /**
    //     * @return Classroom[] Returns an array of Classroom objects
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

    //    public function findOneBySomeField($value): ?Classroom
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function customFindBy($teacherId, $searchVal): array
    {
        if ($teacherId) {
            return $this->createQueryBuilder('c')
                ->andWhere('c.teacherId = :teacherId')
                ->setParameter('teacherId', $teacherId)
                ->andWhere('c.name LIKE :val')
                ->setParameter('val', '%' . $searchVal . '%')
                ->orderBy('c.startDate', 'DESC')
                //->setFirstResult(null)
                //->setMaxResults(null)
                ->getQuery()
                ->getResult();
        } else {
            return $this->createQueryBuilder('c')
                ->andWhere('c.name LIKE :val')
                ->setParameter('val', '%' . $searchVal . '%')
                ->orderBy('c.startDate', 'DESC')
                //->setMaxResults(10)
                ->getQuery()
                ->getResult();
        }
    }
}
