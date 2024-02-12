<?php

namespace App\Controller;

use App\Form\AuthorType;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /*----------------------  ------------------------------------------------------------------------------ */ 
    /*---------------------- AFFICHER ------------------------------------------------------------------------------ */ 
    
    #[Route('/ListAuthor', name: 'ListAuthor')]
    public function listAuthors(Request $request,AuthorRepository $authrepo): Response
    {
        $minBookCount = $request->query->get('min_book_count');
        $maxBookCount = $request->query->get('max_book_count');

        $authorsToDeleteMessage = '';

        if ($request->isMethod('POST') && $request->request->has('delete')) {
            $authrepo->deleteAuthorsWithZeroBookCount();
            $authorsToDeleteMessage = 'Authors with 0 books have been deleted.';
        }
    

        $authors = $authrepo->findAuthorsByBookCountRange($minBookCount, $maxBookCount);
        //$authors = $authrepo->findAllRep(); STANDARD
        //$authors = $authrepo->findAllAuthorsOrderByEmail();

    return $this->render('/author/ListAuthor.html.twig', [
            'authors' => $authors,
            'authorsToDeleteMessage' => $authorsToDeleteMessage,
        ]);
    }
   
    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------- ADD ------------------------------------------------------------------------------ */ 

    #[Route('/AddAuthor', name: 'AddAuthor')]
    public function addAuthor(Request $request, EntityManagerInterface $em): Response
    {
        $author = new Author();
        $author->setNbBooks(0);// Initialisation à zéro
        
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$em = $this->getDoctrine()->getManager(); si em n est pas attribut
            $em->persist($author);
            $em->flush();
            
            $this->addFlash('success', 'Auteur ajouté avec succès.');
            return $this->redirectToRoute('ListAuthor');
        }
        //$this->renderForm ET ->createView()
        return $this->render('/author/AddAuthor.html.twig', [
            'formA' => $form->createView()
            ,'author'=>$author
        ]);
    }

    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------- DELETE ------------------------------------------------------------------------------ */ 

    #[Route('/deleteAuthor/{id}', name: 'deleteAuthor')]
    public function deleteAuthor(Author $author, EntityManagerInterface $em): Response
    {
        $em->remove($author);
        $em->flush();
        return $this->redirectToRoute('ListAuthor');
    }

    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------- EDIT ------------------------------------------------------------------------------ */

    #[Route('/editAuthor/{id}', name: 'editAuthor')]
    public function editAuthor(Author $author, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$em=$this->getDoctrine()->getManager()
            $em->flush();
            return $this->redirectToRoute('ListAuthor');
        }

        return $this->render('/author/EditAuthor.html.twig', [
            'formA' => $form->createView()
        ]);
    }
    /*--------------------------------------------------------------------------------------------------- */ 
   
}
