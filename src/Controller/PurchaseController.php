<?php

namespace App\Controller;

use App\Service\PurchaseManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
