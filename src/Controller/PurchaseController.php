<?php

namespace App\Controller;

use App\Service\PurchaseManagerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class PurchaseController extends ApiController
{
    // getById
    #[Route('/purchase/{id<\d+>}', name: 'get_purchase', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getPurchaseById(int $id, PurchaseManagerService $purchaseManagerSE): Response
    {
        $purchase = $purchaseManagerSE->getPurchaseById($id);

        return $this->response($purchase);
    }

    // getList
    #[Route('/purchase', name: 'get_purchases', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function purchaseList(Request $request, PurchaseManagerService $purchaseManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $this->checkIfHavePagination($request);

        $purchases = $purchaseManagerSE->getPurchasesList($request);

        return $this->response($purchases);
    }

    // post 
    #[Route('/purchase',  name: 'create_purchase', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createPurchase(Request $request, PurchaseManagerService $purchaseManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $parameters = ['title', 'isSpecialEdition', 'status', 'publisher', 'categories'];
        $this->allNeededParametersPresent($request, $parameters);
        $purchaseManagerSE->create($request);

        return $this->respondWithSuccess('Se ha añadido la compra correctamente');
    }

    // put
    #[Route('/purchase/{id<\d+>}', name: 'edit_purchase', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function editPurchase(int $id, Request $request, PurchaseManagerService $purchaseManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $purchaseManagerSE->edit($id, $request);
        return $this->respondWithSuccess('Se ha editado la compra correctamente');
    }

    // delete
    #[Route('/purchase', name: 'delete_purchase', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deletePurchase(Request $request, PurchaseManagerService $purchaseManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $ids = $request->get('ids');

        $purchaseManagerSE->delete($ids);

        if (count($ids) === 1) {
            return $this->respondWithSuccess('Se ha eliminado la compra correctamente');
        }

        return $this->respondWithSuccess('Se han eliminado las compras correctamente');
    }
}
