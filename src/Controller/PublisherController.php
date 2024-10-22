<?php

namespace App\Controller;

use App\Service\PublisherManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class PublisherController extends ApiController
{
    #[Route('/publisher/{id<\d+>}', name: 'app_publisher_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getPublisherById(int $id, PublisherManagerService $publisherManagerSE): Response
    {
        $publisher = $publisherManagerSE->getPublisherById($id);

        return $this->response($publisher);
    }

    // get Publisher list
    #[Route('/publisher', name: 'app_publisher_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function addPublisherList(Request $request, PublisherManagerService $publisherManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $this->checkIfHavePagination($request);

        $publishers = $publisherManagerSE->getPublisherList($request);

        return $this->response($publishers);
    }


    #[Route('/publisher', name: 'app_publisher_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addPublisher(Request $request, PublisherManagerService $publisherManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $request = $this->tokenIdToRequest($request);

        $this->allNeededParametersPresent($request);

        $publisherManagerSE->create($request);

        return $this->respondWithSuccess('Se ha creado el editor correctamente');
    }

    #[Route('/publisher/{id<\d+>}', name: 'app_publisher_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function editPublisher(int $id, Request $request, PublisherManagerService $publisherManagerSE): Response
    {
        $request = $this->transformJsonBody($request);

        $publisherManagerSE->edit($id, $request);

        return $this->respondWithSuccess('Se ha editado el editor correctamente');
    }

    // delete Publisher
    #[Route('/publisher', name: 'app_publisher_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deletePublisher(Request $request, PublisherManagerService $publisherManagerSE): Response
    {
        $request = $this->transformJsonBody($request);

        $ids = $request->get('ids');

        $publisherManagerSE->delete($ids);

        if (count($ids) === 1) {
            return $this->respondWithSuccess('Se ha eliminado el editor correctamente');
        }

        return $this->respondWithSuccess('Se han eliminado los editores correctamente');
    }

    private function allNeededParametersPresent(Request $request): void
    {
        $parameters = ['name', 'user'];
        $missingParameters = [];

        foreach ($parameters as $parameter) {
            if (empty($request->get($parameter))) {
                $missingParameters[] = $parameter;
            }
        }

        if (!empty($missingParameters)) {
            $message = 'Faltan los siguientes parámetros: ' . implode(', ', $missingParameters);
            throw new \Exception($message);
        }
    }
}
