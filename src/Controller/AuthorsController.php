<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Author;

class AuthorsController extends AbstractController
{
    private $em;
    
    public function __construct(ManagerRegistry $doctrine) 
    {
       $this->em = $doctrine->getManager();
    }
    
    #[Route('/authors', name: 'authors')]
    public function index(): Response
    {
        $authors = $this->em->getRepository(Author::class)->findBy(array(), array('id' => 'DESC'),25);
        return $this->render('authors/index.html.twig', [
            'authors' => $authors,
        ]);
    }
    
    #[Route('/authors/{id}', name: 'authors_show')]
    public function show($id): Response
    {
        $author = $this->em->getRepository(Author::class)->findOneBy(array('id' => $id));
        return $this->render('authors/show.html.twig', [
            'author' => $author,
        ]);
    }
}
