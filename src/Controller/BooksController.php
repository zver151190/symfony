<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Book;

class BooksController extends AbstractController
{
    private $em;
    
    public function __construct(ManagerRegistry $doctrine) 
    {
       $this->em = $doctrine->getManager();
    }
    
    #[Route('/books', name: 'books')]
    public function index(): Response
    {
        $books = $this->em->getRepository(Book::class)->findAllWithAuthors();
        
        return $this->render('books/index.html.twig', [
            'books' => $books,
        ]);
    }
}
