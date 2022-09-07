<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BookFixtures extends Fixture
{

    protected $faker;
    
    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create("en_EN");
        for($i = 0; $i < 10; $i++){
            $book = new Book();
            $book->setTitle($this->faker->sentence);
            $book->setDescription($this->faker->paragraphs(5,true));
            $book->setPublishYear($this->faker->date('Y'));
            
            $rand_authors = rand(1,5);
            for($n = 1; $n < $rand_authors; $n++){
                //Add data to pivot table
                $author_num = rand(1,10);
                $book->addAuthor($this->getReference('author_'.$author_num));
            }
            $manager->persist($book);
        }
        $manager->flush();
    }
}
