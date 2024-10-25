<?php

namespace App\Controller;

use App\Service\AuthorManagerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class AuthorController extends ApiController
{
    #[Route('/author/{id<\d+>}', name: 'app_author_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getAuthorById(int $id, AuthorManagerService $authorManagerService): Response
    {
        $author = $authorManagerService->getAuthorById($id);

        return $this->response($author);
    }


    #[Route('/author', name: 'app_author_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function authorList(Request $request, AuthorManagerService $authorManagerService): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $this->checkIfHavePagination($request);

        $authors = $authorManagerService->getAuthorList($request);

        return $this->response($authors);
    }


    #[Route('/author', name: 'app_author_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addAuthor(Request $request, AuthorManagerService $authorManagerService): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $parameters = ['name'];
        $this->allNeededParametersPresent($request, $parameters);

        $authorManagerService->create($request);

        return $this->respondWithSuccess('Se ha creado el autor correctamente');
    }
}
