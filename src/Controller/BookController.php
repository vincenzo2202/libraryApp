<?php

namespace App\Controller;

use App\Service\BookManagerService;
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
}
