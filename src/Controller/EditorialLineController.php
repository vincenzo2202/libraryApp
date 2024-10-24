<?php

namespace App\Controller;

use App\Service\EditorialLineManagerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class EditorialLineController extends ApiController
{
    // get Editorial Line by id
    #[Route('/editorial/{id<\d+>}', name: 'app_editorialLine_show', methods: ['GET'])]
    public function getEditorialLineById(int $id, EditorialLineManagerService $editorialLineManagerSE): Response
    {
        $editorialLine = $editorialLineManagerSE->getEditorialLineById($id);

        return $this->response($editorialLine);
    }

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
