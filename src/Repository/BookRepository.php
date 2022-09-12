<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function add(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function remove(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function findBooksWithOneOrLessAuthorsSQL(): ? array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT b,ba,COUNT(ba.id) as counter
            FROM App\Entity\Book b
            LEFT JOIN b.authors ba
            GROUP BY b.id
            HAVING counter < 2
            ORDER BY b.id DESC'
        )
        ->setMaxResults(25);
        return $query->getResult();
    }
    
    public function findBooksWithOneOrLessAuthors(): ? array
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        return $qb->select('b,ba,COUNT(ba.id) as counter')
           ->from('App\Entity\Book', 'b')
           ->leftJoin('b.authors', 'ba')
           ->groupBy('b.id')
           ->having('counter < 2')
           ->setMaxResults(25)
           ->getQuery()
           ->getResult();
    }
    
    public function findAllWithAuthors(): ?array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT b, a, GROUP_CONCAT(a.name SEPARATOR ', ')
            FROM App\Entity\Book b
            LEFT JOIN b.authors a
            GROUP BY b.id
            ORDER BY b.id DESC'
        )
        ->setMaxResults(30);
        return $query->getResult();
    }


    public function test(Book $entity): ?array
    {
        return $entity->getAuthors()->toArray();
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->update('App\Entity\Author','a')
           ->innerJoin('a.books', 'b')
           ->set('a.total_books', '3')
           ->where('b.id = 440');
    }
    
    public function updateAuthorsTotalBooks(Book $entity): void
    {
        // $entityManager = $this->getEntityManager();
        // $qb = $entityManager->createQueryBuilder();
        // return $gb->update();
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
//            xd
//        ;
//    }
}
