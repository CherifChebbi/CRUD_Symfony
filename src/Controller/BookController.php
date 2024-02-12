<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /*--------------------------------------------------------------------------------------------------- */ 
    /*----------------------AFFICHER BOOKS  ------------------------------------------------------------------------------ */ 

    #[Route('/ListBook', name: 'ListBook')]
    public function listBooks(EntityManagerInterface $em, BookRepository $bookrepo, Request $request): Response
    {
        

        //$books = $bookrepo->findBy(['published' => true]);
        //$books = $bookrepo->findBooksBefore2023WithAuthorMoreThan35Books();
        $books = $bookrepo->findAllOrderedByAuthor();
        
        /*
        $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');
        $books = $bookrepo->findBooksBetweenDates($startDate, $endDate);
        */
        
        //Search
        $ref = $request->query->get('ref');
        if ($ref) {
            $books = $bookrepo->findByRef($ref);
        }

        $sum = $bookrepo->sumBooksInScienceFictionCategory();
        $numPublishedBooks = count($books);
        $numUnPublishedBooks = count($bookrepo->findBy(['published' => false]));

        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('ListBook');
        }

        $searchLabel = $request->query->get('label');
        if ($searchLabel) {
            $books = $bookrepo->findByRef($searchLabel);
        }
        

        return $this->render('/book/ListBook.html.twig', [
            'formB' => $form->createView(),
            'books' => $books,
            'sum' => $sum,
            'numPublishedBooks' => $numPublishedBooks,
            'numUnPublishedBooks' => $numUnPublishedBooks,
        ]);
    }
    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------- ADD BOOK ------------------------------------------------------------------------------ */ 

    #[Route('/AddBook', name: 'AddBook')]
    public function createBook(EntityManagerInterface $em, Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez l'auteur associé au livre 
            $author = $book->getAuthor();

            // NBR livre++
            $author->setNbBooks($author->getNbBooks() + 1);

            // Persistez le livre et l'auteur
            $em->persist($book);
            $em->persist($author);
            $em->flush();

            return $this->redirectToRoute('ListBook');
        }

        return $this->render('/book/AddBook.html.twig', [
            'formB' => $form->createView(),
        ]);
    }


    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------- DELETE BOOK ------------------------------------------------------------------------------ */ 

    #[Route('/DeleteBook/{ref}', name: 'deleteBook')]
    public function deleteBook(Book $book, EntityManagerInterface $em): Response
    {
        //Récupérez l'auteur associé au livre
        $author = $book->getAuthor();

        if ($author) {
            // nbr book-- >0
            $nbBooks = $author->getNbBooks();
            if ($nbBooks > 0) {
                $author->setNbBooks($nbBooks - 1);
            }

            // Dissociez le livre de l'auteur
            $book->setAuthor(null);

            // Persistez les modifications
            $em->persist($author);
            $em->persist($book);

            // Supprimez le livre
            $em->remove($book);
            $em->flush();

            $this->addFlash('success', 'Book deleted.');
        } else {
            // Gérez la situation où le livre n'a pas d'auteur (si nécessaire)
            $em->remove($book);
            $em->flush();
            $this->addFlash('success', 'Book deleted.');
        }

        return $this->redirectToRoute('ListBook');
    }
    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------- EDIT BOOK  ------------------------------------------------------------------------------ */ 

    #[Route('/EditBook/{ref}', name: 'editBook')]
    public function editBook(Book $book, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez Ancien auteur 
            $ancienAuteur = $book->getAuthor();

            // Persistez le livre
            $em->persist($book);

            // Récupérez le nouvel auteur
            $nouvelAuteur = $book->getAuthor();

            if ($ancienAuteur !== $nouvelAuteur) {
                // Si l'ancien auteur existe, nbr book--
                if ($ancienAuteur) {
                    // Vérifiez existance
                    $ancienAuteur->setNbBooks($ancienAuteur->getNbBooks() - 1);
                    $em->persist($ancienAuteur);
                }

                // SI EXISTE nbr--
                if ($nouvelAuteur) {
                    // Vérifiez que le nouvel auteur existe avant de l'incrémenter
                    $nouvelAuteur->setNbBooks($nouvelAuteur->getNbBooks() + 1);
                    $em->persist($nouvelAuteur);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Book modified.');
            return $this->redirectToRoute('ListBook');
        }

        return $this->render('/book/EditBook.html.twig', [
            'formB' => $form->createView(),
        ]);
    }

    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------- SHOW BOOK ------------------------------------------------------------------------------ */ 

    #[Route('/showBook/{ref}', name: 'showBook')]

    public function showBook($ref, BookRepository $repository)
    {
        $book = $repository->find($ref);
        if (!$book) {
            return $this->redirectToRoute('ListBook');
        }

        return $this->render('/book/show.html.twig', ['book' => $book]);
    }

    /*--------------------------------------------------------------------------------------------------- */ 
    /*---------------------------------------------------------------------------------------------------- */ 

}
