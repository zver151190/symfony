<?php
namespace App\EventListener;

use App\Entity\Book;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class BookChangedListener
{
    public function postPersist(LifecycleEventArgs $args): void
    {
        var_dump('post persist');
    }
}