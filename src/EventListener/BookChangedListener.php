<?php
namespace App\EventListener;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class BookChangedListener
{
    
    //This function is called after we save a new book
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();

        //Check if this is a book
        $entity = $args->getObject();
        
        //If we persist a book entity
        if ($entity instanceof Book) {
            //Flush the curren object to get the most current data
            $entityManager->flush();
            //Get book's authors and update their totalBooks counter
            foreach($entity->getAuthors() as $author){
                $author->updateTotalBooks();
            }
            $entityManager->flush();
        }
        //If we persist a author entity
        else if($entity instanceof Author){
            foreach($entity->getBooks() as $book){
                $book->addAuthor($entity);
            }
            $entity->updateTotalBooks();
            $entityManager->flush();
        }
    }
    
    
    //This function is called after we edit a book or author entites
    public function postUpdate(LifecycleEventArgs $args): void
    {
        //Flush the curren object to get the most current data
        $entityManager = $args->getObjectManager();
        $entityManager->flush();
        
        //Check if this is a book
        $entity = $args->getObject();
        if ($entity instanceof Book) {
            //Get book's authors and update their totalBooks counter
            foreach($entity->getAuthors() as $author){
                $author->updateTotalBooks();
            }
            $entityManager->flush();
        }
    }
}