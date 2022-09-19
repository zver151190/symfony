<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Author;
use App\Entity\Book;
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


    #[Route('/authors/create', name: 'authors_create')]
    public function create(Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) { 
          $this->em->persist($author);
          $this->em->flush(); 
          return $this->redirectToRoute('authors');
       }
        
        return $this->render('authors/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route("/authors/edit/{id}", name:"authors_edit")]
    public function edit($id, Request $request): Response
    {
        //Get author by id we received
        $author = $this->em->getRepository(Author::class)->findOneBy(array('id' => $id));
        
        //Get all author books
        $authorBooks = $author->getBooks();
        
        //Save this value to compare if the value has been changed
        //If this value has been changed - we need to update totalBooks counter
        $oldAuthorBooks = clone($authorBooks);
        
        //Load edit form from this author
        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);

        //Check if we submitted the form
        if($form->isSubmitted() && $form->isValid()) {
            
            //Check if the books has changed by finding if there is any difference between
            //the old array adn the new and vice versa
            if(array_diff($oldAuthorBooks->toArray(), $authorBooks->toArray()) ||
                array_diff($authorBooks->toArray(), $oldAuthorBooks->toArray())
            ){
                //Remove all books from the author
                foreach($oldAuthorBooks as $book){
                    $book->removeAuthor($author);
                }
                
                //Update the books author by calling the set method
                //Because apparently the only the owning side can update ManyToMany relationship
                foreach($authorBooks as $book){
                    $book->addAuthor($author);
                }
            
            }

            $this->em->flush(); 
            return $this->redirectToRoute('authors');
        }

        return $this->render('authors/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
