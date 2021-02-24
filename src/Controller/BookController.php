<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Author;
use App\Entity\Book;

class BookController extends AbstractController
{
    /**
     * @Route("/book", name="book_index")
     */
    public function index(Request $r): Response
    {

        $books = $this->getDoctrine()
        ->getRepository(Book::class);

        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findAll();

        if ($r->query->get('author_id') !== null){
            if ($r->query->get('author_id') == 'view_all'){
                $books = $books->findBy([],['title'=>'asc']);;
            } else {
                $books = $books->findBy(['author_id' => $r->query->get('author_id')], ['title' => 'asc']);
            }
        } else {
            $books = $books->findBy([],['title'=>'asc']);;
        }

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'authors' => $authors,
            'author_id' => $r->query->get('author_id') ?? '0',
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
    * @Route("/book/create", name="book_create", methods={"GET"})
    */
    public function create(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $book_title = $r->getSession()->getFlashBag()->get('book_title', []);
        $book_isbn = $r->getSession()->getFlashBag()->get('book_isbn', []);
        $book_pages = $r->getSession()->getFlashBag()->get('book_pages', []);
        $book_about = $r->getSession()->getFlashBag()->get('book_about', []);
        $book_author_id = $r->getSession()->getFlashBag()->get('book_author_id', []);

        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findBy([],['surname'=>'asc']);

        return $this->render('book/create.html.twig', [
            'authors' => $authors,
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'book_title' => $book_title[0] ?? '',
            'book_isbn' => $book_isbn[0] ?? '',
            'book_pages' => $book_pages[0] ?? '',
            'book_about' => $book_about[0] ?? '',
            'book_author_id' => $book_author_id[0] ?? ''
        ]);
    }

    /**
    * @Route("/book/store", name="book_store", methods={"POST"})
    */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $submittedToken = $r->request->get('token');

        if (!$this->isCsrfTokenValid('token', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Bad token - CSRF');
            return $this->redirectToRoute('book_create');
        }

        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($r->request->get('book_author_id'));

        if($author == null){
            $r->getSession()->getFlashBag()->add('errors', "Author must be selected.");
        }

        $book = new Book;

        $book->
        setTitle($r->request->get('book_title'))->
        setIsbn($r->request->get('book_isbn'))->
        setPages((int)$r->request->get('book_pages'))->
        setShortDescription($r->request->get('book_about'))->
        setAuthor($author);

        $errors = $validator->validate($book);

        if (count($errors) > 0) {
            foreach ($errors as $key => $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }

            $r->getSession()->getFlashBag()->add('book_title', $r->request->get('book_title'));
            $r->getSession()->getFlashBag()->add('book_isbn', $r->request->get('book_isbn'));
            $r->getSession()->getFlashBag()->add('book_pages', $r->request->get('book_pages'));
            $r->getSession()->getFlashBag()->add('book_about', $r->request->get('book_about'));
            $r->getSession()->getFlashBag()->add('book_author_id', $r->request->get('book_author_id'));

            return $this->redirectToRoute('book_create');
        }

        if($author == null){
            $r->getSession()->getFlashBag()->add('book_title', $r->request->get('book_title'));
            $r->getSession()->getFlashBag()->add('book_isbn', $r->request->get('book_isbn'));
            $r->getSession()->getFlashBag()->add('book_pages', $r->request->get('book_pages'));
            $r->getSession()->getFlashBag()->add('book_about', $r->request->get('book_about'));
            return $this->redirectToRoute('book_create');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Book has been added succesfully.');

        return $this->redirectToRoute('book_index');
    }

    /**
    * @Route("/book/edit/{id}", name="book_edit", methods={"GET"})
    */
    public function edit(Request $r, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $book = $this->getDoctrine()
        ->getRepository(Book::class)
        ->find($id);

        $book_title = $r->getSession()->getFlashBag()->get('book_title', []);
        $book_isbn = $r->getSession()->getFlashBag()->get('book_isbn', []);
        $book_pages = $r->getSession()->getFlashBag()->get('book_pages', []);
        $book_about = $r->getSession()->getFlashBag()->get('book_about', []);
        $book_author_id = $r->getSession()->getFlashBag()->get('book_author_id', []);

        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findBy([],['surname'=>'asc']);

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'authors' => $authors,
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'book_title' => $book_title[0] ?? '',
            'book_isbn' => $book_isbn[0] ?? '',
            'book_pages' => $book_pages[0] ?? '',
            'book_about' => $book_about[0] ?? '',
            'book_author_id' => $book_author_id[0] ?? ''
        ]);
    }

    /**
    * @Route("/book/update/{id}", name="book_update", methods={"POST"})
    */
    public function update(Request $r, $id, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $submittedToken = $r->request->get('token');

        if (!$this->isCsrfTokenValid('token', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Bad token - CSRF');
            return $this->redirectToRoute('book_edit', ['id'=>$book->getId()]);
        }

        $book = $this->getDoctrine()
        ->getRepository(Book::class)
        ->find($id);

        $book->
        setTitle($r->request->get('book_title'))->
        setIsbn($r->request->get('book_isbn'))->
        setPages((int)$r->request->get('book_pages'))->
        setShortDescription($r->request->get('book_about'))->
        setAuthorId($r->request->get('book_author_id'));

        $errors = $validator->validate($book);

        if (count($errors) > 0) {
            foreach ($errors as $key => $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }

            $r->getSession()->getFlashBag()->add('book_title', $r->request->get('book_title'));
            $r->getSession()->getFlashBag()->add('book_isbn', $r->request->get('book_isbn'));
            $r->getSession()->getFlashBag()->add('book_pages', $r->request->get('book_pages'));
            $r->getSession()->getFlashBag()->add('book_about', $r->request->get('book_about'));
            $r->getSession()->getFlashBag()->add('book_author_id', $r->request->get("book_author_id"));

            return $this->redirectToRoute('book_edit', ['id'=>$book->getId()]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Book changes has been saved.');


        return $this->redirectToRoute('book_index');
    }

    /**
    * @Route("/book/delete/{id}", name="book_delete", methods={"POST"})
    */
    public function delete(Request $r, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $submittedToken = $r->request->get('token');

        if (!$this->isCsrfTokenValid('token', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Bad token - CSRF');
            return $this->redirectToRoute('book_index');
        }     
        
        $book = $this->getDoctrine()
        ->getRepository(Book::class)
        ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Book has been successfully deleted.');


        return $this->redirectToRoute('book_index');
    }


}
