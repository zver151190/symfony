<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Author;
use App\Form\AuthorFormType;

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

    #[Route("/authors/edit/{id}", name:"authors_edit")]
    public function edit($id, Request $request): Response
    {
        
        $author = $this->em->getRepository(Author::class)->findOneBy(array('id' => $id));
        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);
;
        if($form->isSubmitted() && $form->isValid()) {
            //Update the books author by calling the set method
            //Because apparently the only the owning side can update ManyToMany relationship
            foreach($author->getBooks() as $book){
                $book->addAuthor($author);
            }
            $this->em->flush(); 
            return $this->redirectToRoute('authors');
        }

        return $this->render('authors/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
