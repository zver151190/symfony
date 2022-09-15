<?php
namespace App\EventListener;

use App\Entity\Author;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class AuthorChangedListener
{
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        if(method_exists($entity,'getAuthors')){
            foreach($entity->getAuthors() as $author){
                $author->updateTotalBooks();
            }
        }
        $entityManager->persist($entity); 
        $entityManager->flush();
    }
}