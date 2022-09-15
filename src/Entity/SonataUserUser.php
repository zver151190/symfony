<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser;


#[ORM\Entity()]
#[ORM\Table(name: 'sonata_user__user')]
class SonataUserUser extends BaseUser
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;
}