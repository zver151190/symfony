<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Book;
use App\Form\BookFormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BooksController extends AbstractController
{
    private $em;
    
    public function __construct(ManagerRegistry $doctrine, SluggerInterface $slugger) 
    {
       $this->em = $doctrine->getManager();
       $this->slugger = $slugger;
       
    }
    
    #[Route('/books', name: 'books', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $books = $this->em->getRepository(Book::class)->findBy(array(), array('id' => 'DESC'),25);
        $book = new Book();
        $form = $this->createForm(BookFormType::class, $book);
        return $this->render('books/index.html.twig', [
            'books' => $books,
        ]);
    }
    
    #[Route('/books', name: 'books_search', methods: ['POST'])]
    public function search(Request $request): Response
    {
        $post = $request->request->all();
        $filter = [];
        if(isset($post['filters'])) $filter = $post['filters'];
        $books = $this->em->getRepository(Book::class)->findByFilter($filter);
        return $this->render('books/index.html.twig', [
            'books' => $books,
        ]);
    }
    

    #[Route('/books/create', name: 'books_create')]
    public function create(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) { 
          $book = $form->getData();
          $image = $form->get('cover')->getData();
          if($image){
            $safeFilename = $this->slugger->slug($book->getTitle());
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
            try{
                $image->move(
                    $this->getParameter('book_cover_directory'),
                    $newFilename
                );
            }catch(FileException $e){
                  return new Response($e->getMessage());
            }
            $book->setCover('/public/uploads/' . $newFilename);
          }
          $this->em->persist($book);
          $this->em->flush(); 
          return $this->redirectToRoute('books');
       }
        
        return $this->render('books/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route("/books/edit/{id}", name:"books_edit")]
    public function edit($id, Request $request): Response
    {
        
        $book = $this->em->getRepository(Book::class)->findOneBy(array('id' => $id));
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);
        $image = $form->get('cover')->getData();
        if($form->isSubmitted() && $form->isValid()) { 
            //Check if we passed a new cover image
            if($image){
                if($book->getCover() !== null){
                    $oldFile = str_replace('/public/uploads/','',$book->getCover());
                    if(file_exists($this->getParameter('book_cover_directory').$oldFile)){
                        $safeFilename = $this->slugger->slug($book->getTitle());
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                        try{
                            $image->move(
                                $this->getParameter('book_cover_directory'),
                                $newFilename
                            );
                        }catch(FileException $e){
                              return new Response($e->getMessage());
                        }
                        //Delete the old file
                        unlink($this->getParameter('book_cover_directory').$oldFile);
                        //Save new as cover
                        $book->setCover('/public/uploads/' . $newFilename);
                        
                        $this->em->flush(); 
                        return $this->redirectToRoute('books');
                    }
                }
            }else{
                $this->em->flush(); 
                return $this->render('books/edit.html.twig', [
                    'form' => $form->createView()
                ]);
            }
        }

        return $this->render('books/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
    

}
