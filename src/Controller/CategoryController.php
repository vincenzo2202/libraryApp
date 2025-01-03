<?php

namespace App\Controller;

use App\Service\CategoryManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class CategoryController extends ApiController
{
    #[Route('/category/{id<\d+>}', name: 'app_category_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getCategoryById(int $id, CategoryManagerService $categoryManagerSE): Response
    {
        $category = $categoryManagerSE->getCategoryById($id);

        return $this->response($category);
    }

    #[Route('/category', name: 'app_category_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function addCategoryList(Request $request, CategoryManagerService $categoryManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $this->checkIfHavePagination($request);

        $categories = $categoryManagerSE->getCategoryList($request);

        return $this->response($categories);
    }


    #[Route('/category', name: 'app_category_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addCategory(Request $request, CategoryManagerService $categoryManagerSE): Response
    {
        $request = $this->transformJsonBody($request);

        $me = $this->getUser()->getId();
        $request->request->add(['user' => $me]);
        $parameters = ['name', 'color'];
        $this->allNeededParametersPresent($request, $parameters);

        $categoryManagerSE->create($request);

        return $this->respondWithSuccess('Se ha creado la categoría correctamente');
    }

    #[Route('/category/{id<\d+>}', name: 'app_category_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function editCategory(int $id, Request $request, CategoryManagerService $categoryManagerSE): Response
    {
        $request = $this->transformJsonBody($request);

        $categoryManagerSE->edit($id, $request);

        return $this->respondWithSuccess('Se ha editado la categoría correctamente');
    }

    #[Route('/category', name: 'app_category_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteCategory(Request $request, CategoryManagerService $categoryManagerSE): Response
    {
        $request = $this->transformJsonBody($request);

        $ids = $request->get('ids');

        $categoryManagerSE->delete($ids);

        // si el array solo tiene un elemento, se devuelve un string
        if (count($ids) === 1) {
            return $this->respondWithSuccess('Se ha eliminado la categoría correctamente');
        }

        return $this->respondWithSuccess('Se han eliminado las categorías correctamente');
    }
}
