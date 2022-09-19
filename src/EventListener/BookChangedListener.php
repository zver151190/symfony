<?php
namespace App\EventListener;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\ManagerRegistry;

class BookChangedListener
{
    
    private $em;
    
    public function __construct(ManagerRegistry $doctrine) 
    {
       $this->em = $doctrine->getManager();
    }
    
    //This function is called after we save a new book
    public function postPersist(LifecycleEventArgs $args): void
    {

        //Check if this is a book
        $entity = $args->getObject();
        
        //If we persist a book entity
        if ($entity instanceof Book) {
            //Flush the curren object to get the most current data
            $this->em->flush();
            //Get book's authors and update their totalBooks counter
            foreach($entity->getAuthors() as $author){
                $this->updateAuthorTotalBooks($author);
            }
            $this->em->flush();
        }
        //If we persist a author entity
        else if($entity instanceof Author){
            foreach($entity->getBooks() as $book){
                $book->addAuthor($entity);
            }
            $entity->updateTotalBooks();
            $this->em->flush();
        }
    }
    
    //This function makes sure we update the authors that were removed
    //from the book, and that we are subtracting their totalBooks counter 
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        
        $entity = $args->getObject();
        $oldAuthors = $this->getBookOldAuthors($entity);
        
        if ($entity instanceof Book) {
            $newAuthors = [];
            foreach($entity->getAuthors() as $author){
                $newAuthors[]= $author->getId();
            }
            
            $diff = array_diff($oldAuthors, $newAuthors);
            
            foreach($diff as $id){
                $this->subtractAuthorTotalBooks($id);
            }
        }
        
    }    

    //This function is called after we edit a book or author entites
    public function postUpdate(LifecycleEventArgs $args): void
    {
        //Flush the curren object to get the most current data
        $this->em->flush();
        
        //Check if this is a book
        $entity = $args->getObject();


        if ($entity instanceof Book) {
            
            //Get book's authors and update their totalBooks counter
            foreach($entity->getAuthors() as $author){
                $this->updateAuthorTotalBooks($author);
            }
            $this->em->flush();
        }
    }
    
    public function getBookOldAuthors(Book $book){
        return $this->em->getRepository(Book::class)->findBookAuthorsId($book->getId());
    }
    public function updateAuthorTotalBooks(Author $author){
        $this->em->getRepository(Author::class)->updateTotalBooks($author->getId());
    }
    public function subtractAuthorTotalBooks($id){
        $this->em->getRepository(Author::class)->subtractAuthorTotalBooks($id);
    }
    
}