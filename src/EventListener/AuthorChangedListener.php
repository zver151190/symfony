<?php
namespace App\EventListener;

use App\Entity\Author;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class AuthorChangedListener
{
    public function postUpdate(LifecycleEventArgs $args): void
    {
        //Check if this is a author
        $entity = $args->getObject();
        
        if ($entity instanceof Author) {
            dd($entity);
        }
    }
}