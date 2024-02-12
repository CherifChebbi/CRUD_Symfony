<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ServiceEntityRepository<Author>
 *
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[]    findAll()
 * @method Author[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

//    /**
//     * @return Author[] Returns an array of Author objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Author
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/*----------------------------------------*/
/*-----QueryBuilder---------*/

    public function findAllRep():array
    {
            return $this->createQueryBuilder('s')
        ->orderBy('s.email','ASC')
        ->getQuery()
        ->getResult() ;
        
    }
    public function findAllAuthorsOrderByEmail()
    {
            return $this->createQueryBuilder('a')
        ->orderBy('a.email', 'ASC')
        ->getQuery()
        ->getResult();
    }

/*----------------------------------------*/
/*-----DQL (Doctrine Query Language)---------*/

    public function findAuthorsByBookCountRange($minBookCount, $maxBookCount)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('
            SELECT a
            FROM App\Entity\Author a
            WHERE a.nb_books >= :minBookCount
            AND a.nb_books <= :maxBookCount
        ')
            ->setParameter('minBookCount', $minBookCount)
            ->setParameter('maxBookCount', $maxBookCount);

        return $query->getResult();
    }
    public function deleteAuthorsWithZeroBookCount()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('
            DELETE FROM App\Entity\Author a
            WHERE a.nb_books = 0
        ');

        $query->execute();
    }
/*----------------------------------------*/

}
