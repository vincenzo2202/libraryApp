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

    // add author
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
