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
        $this->faker = Factory::create("en_US");
        for($i = 0; $i < 100; $i++){
            $book = new Book();
            $book->setTitle($this->faker->sentence);
            $book->setCover($this->faker->imageUrl(640, 480, $this->faker->catchPhrase, true));
            $book->setDescription($this->faker->paragraphs(5,true));
            $book->setPublishYear($this->faker->date('Y'));
            
            $rand_authors = rand(1,3);
            for($n = 1; $n < $rand_authors; $n++){
                //Add data to pivot table
                $author_num = rand(0,100);
                $book->addAuthor($this->getReference('author_'.$author_num));
            }
            $manager->persist($book);
        }
        $manager->flush();
    }
}
