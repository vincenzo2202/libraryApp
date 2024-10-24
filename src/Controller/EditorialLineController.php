<?php

namespace App\Controller;

use App\Service\EditorialLineManagerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class EditorialLineController extends ApiController
{
    // get Editorial Line by id
    #[Route('/editorial/{id<\d+>}', name: 'app_editorialLine_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getEditorialLineById(int $id, EditorialLineManagerService $editorialLineManagerSE): Response
    {
        $editorialLine = $editorialLineManagerSE->getEditorialLineById($id);

        return $this->response($editorialLine);
    }

    // get Editorial Line list
    #[Route('/editorial', name: 'app_editorialLine_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function addEditorialLineList(Request $request, EditorialLineManagerService $editorialLineManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $this->checkIfHavePagination($request);

        $editorialLines = $editorialLineManagerSE->getEditorialLineList($request);

        return $this->response($editorialLines);
    }


    #[Route('/editorial', name: 'app_editorialLine_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
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
