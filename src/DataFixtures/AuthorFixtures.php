<?php

namespace App\DataFixtures;

use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AuthorFixtures extends Fixture
{

    protected $faker;
    
    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create("en_EN");
        for($i = 1; $i < 11; $i++){
            $author = new Author();
            $author->setName($this->faker->name);
            $manager->persist($author);
            
            $ref = 'author_'.$i;
            $this->addReference($ref,$author);
        }
        $manager->flush();
    }
}
