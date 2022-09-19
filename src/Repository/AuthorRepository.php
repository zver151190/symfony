<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function add(Author $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Author $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    //Subtructs 1 from author total book counter
    public function subtractAuthorTotalBooks($id): void
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
             "UPDATE App\Entity\Author a
              SET a.totalBooks = a.totalBooks - 1
              WHERE a.id = '{$id}'"
        );
        $result = $query->execute();  
    }
    
    
    //Recalculate authors fiels totalBooks
    public function updateTotalBooks($id): void
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $total = $qb->select('COUNT(b.id) as total')
           ->from('App\Entity\Book', 'b')
           ->leftJoin('b.authors', 'ba')
           ->where('ba.id = :id')
           ->setParameter(':id', $id)
           ->getQuery()
           ->setMaxResults(1)
           ->getResult();
         if(isset($total[0]['total'])) $total = $total[0]['total'];
         else $total = 0;
         
         
        $qb = $entityManager->createQueryBuilder();
        $query = $qb->update('App\Entity\Author', 'a')
                ->set('a.totalBooks', ':total')
                ->where('a.id = :id')
                ->setParameter('total', $total)
                ->setParameter('id', $id)
                ->getQuery();
        $result = $query->execute();
    }
}
