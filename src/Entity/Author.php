<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ORM\Table(name: 'authors')]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'authors', cascade: ["persist"])]
    private Collection $books;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $totalBooks = 0;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }
    
    
    public function emptyBooks(): void
    {
        $books = $this->getBooks();
        foreach($books->toArray() as $book){
            $book->removeAuthor($this);
        }
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->addAuthor($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->removeElement($book)) {
            $book->removeAuthor($this);
        }

        return $this;
    }

    public function getTotalBooks(): ?int
    {
        return $this->totalBooks;
    }

    public function updateTotalBooks(): void
    {
        $totalBooks = count($this->getBooks()); 
        $this->setTotalBooks($totalBooks);
    }
    
    public function setTotalBooks(int $totalBooks): self
    {
        $this->totalBooks = $totalBooks;

        return $this;
    }
    
    public function getBooksArray(): Array
    {
        $arr = [];
        foreach($this->books as $book){
            $arr[] = $book->getTitle();
        }
        return $arr;
    }
    
    public function __toString(){
        return $this->getName();
    }
    
}
