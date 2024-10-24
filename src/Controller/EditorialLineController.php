<?php

namespace App\Controller;

use App\Service\EditorialLineManagerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class EditorialLineController extends ApiController
{
    //  add editorialLine
    #[Route('/editorial', name: 'app_editorialLine_create', methods: ['POST'])]
    public function addEditorialLine(Request $request, EditorialLineManagerService $editorialLineManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $parameters = ['name', 'color'];
        $this->allNeededParametersPresent($request, $parameters);

        $editorialLineManagerSE->create($request);

        return $this->respondWithSuccess('Se ha creado la línea editorial correctamente');
    }
}
