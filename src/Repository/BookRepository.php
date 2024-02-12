<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/*----------------------------------------*/
/*-----QueryBuilder---------*/
    public function findAllOrderedByAuthor()
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->orderBy('a.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findBooksBefore2023WithAuthorMoreThan35Books()
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.ref', 'b.title', 'b.Category', 'b.published', 'b.publicationDate', 'a.username as username')
            ->join('b.author', 'a')
            ->where('b.publicationDate < :year2023')
            ->groupBy('a.id')
            ->having('COUNT(b) > 35')
            ->setParameter('year2023', new \DateTime('2023-01-01'));

        return $qb->getQuery()->getResult();
    }
/*----------------------------------------*/
/*-----DQL (Doctrine Query Language)---------*/

    public function sumBooksInScienceFictionCategory()
    {
        $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery('
                SELECT COUNT(b) as total
                FROM App\Entity\Book b
                WHERE b.Category = :category
            ')
                ->setParameter('category', 'Science-Fiction');

            return $query->getSingleScalarResult();
    }
    public function findBooksBetweenDates($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('
            SELECT b
            FROM App\Entity\Book b
            WHERE b.publicationDate >= :startDate
            AND b.publicationDate <= :endDate
        ')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $query->getResult();
    }
/*----------------------------------------*/
}
