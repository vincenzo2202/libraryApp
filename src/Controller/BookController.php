<?php

namespace App\Controller;

use App\Service\BookManagerService;
use App\Service\PurchaseManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class BookController extends ApiController
{
    //   getById
    #[Route('/book/{id<\d+>}', name: 'get_book', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getBookById(int $id, BookManagerService $bookManagerSE): Response
    {
        $book = $bookManagerSE->getBooksById($id);

        return $this->response($book);
    }

    //   getList
    #[Route('/book', name: 'get_books', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function bookList(Request $request, BookManagerService $bookManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $this->checkIfHavePagination($request);

        $books = $bookManagerSE->getBooksList($request);

        return $this->response($books);
    }

    //   post
    #[Route('/book', name: 'create_book', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createBook(Request $request, BookManagerService $bookManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $parameters = ['title', 'isSpecialEdition', 'status', 'author', 'publisher', 'categories'];
        $this->allNeededParametersPresent($request, $parameters);
        $bookManagerSE->create($request);


        return $this->respondWithSuccess('Se ha añadido el libro correctamente');
    }
    //   put
    #[Route('/book/{id<\d+>}', name: 'edit_book', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function editBook(int $id, Request $request, BookManagerService $bookManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $bookManagerSE->edit($id, $request);
        return $this->respondWithSuccess('Se ha editado el libro correctamente');
    }

    //   delete
    #[Route('/book', name: 'delete_book', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteBook(Request $request, BookManagerService $bookManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $ids = $request->get('ids');

        $bookManagerSE->delete($ids);

        if (count($ids) === 1) {
            return $this->respondWithSuccess('Se ha eliminado el libro correctamente');
        }

        return $this->respondWithSuccess('Se han eliminado los libros correctamente');
    }
}
