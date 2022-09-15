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
    
    public function findByFilter($filters): ? array
    {
        $em = $this->getEntityManager();
        $entityFields = $properties = $em->getClassMetadata('App\Entity\Book')->getFieldNames();
        $entityFields['authors'] = 'authors';
        $pass = [];
        $columns = [];
        $qb = $em->createQueryBuilder();
        $hasWhere = false;
        $qb->select('b,ba')
            ->from('App\Entity\Book', 'b')
            ->leftJoin('b.authors', 'ba');
        foreach($filters as $filter){
            if(in_array($filter['key'],$entityFields)){
                if($filter['key'] == 'authors'){
                    if($hasWhere){
                        $qb->andWhere("ba.name LIKE '%{$filter['value']}%'");
                    }else{
                        $qb->where("ba.name LIKE '%{$filter['value']}%'");
                        $hasWhere = true;
                    }
                }else{
                    if($hasWhere){
                        $qb->andWhere("b.{$filter['key']} LIKE '%{$filter['value']}%'");
                    }else{
                        $qb->where("b.{$filter['key']} LIKE '%{$filter['value']}%'");
                        $hasWhere = true;
                    }
                }
            }
        }
        return $qb->setMaxResults(25)
           ->getQuery()
           ->getResult();
    }
    
    public function findBooksWithOneOrMoreAuthorsSQL(): ? array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT b,ba,COUNT(ba.id) as counter
            FROM App\Entity\Book b
            LEFT JOIN b.authors ba
            GROUP BY b.id
            HAVING counter > 2
            ORDER BY b.id DESC'
        )
        ->setMaxResults(25);
        return $query->getResult();
    }
    
    public function findBooksWithOneOrMoresAuthors(): ? array
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        return $qb->select('b,ba,COUNT(ba.id) as counter')
           ->from('App\Entity\Book', 'b')
           ->leftJoin('b.authors', 'ba')
           ->groupBy('b.id')
           ->having('counter > 2')
           ->setMaxResults(25)
           ->getQuery()
           ->getResult();
    }


    public function findAllQueryBuilder($filter = '')
    {
        $qb = $this->createQueryBuilder('books');
        if ($filter) {
            $qb->andWhere('programmer.nickname LIKE :filter OR programmer.tagLine LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }
        return $qb;
    }
    
}
