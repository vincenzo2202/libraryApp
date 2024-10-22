<?php

namespace App\Service;

use App\Entity\Publisher;
use App\Exception\NotFoundException;
use App\Repository\PublisherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class PublisherManagerService
{
    private $security;

    public function __construct(
        private PublisherRepository $publisherRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function getPublisherById($id)
    {
        $publisher = $this->publisherRE->findOrFail($id);
        $me = $this->tokenUserId();
        if ($publisher->getUser()->getId() !== $me) {
            throw new NotFoundException('Publisher no encontrado');
        }

        $formatedPublisher = [
            'id' => $publisher->getId(),
            'name' => $publisher->getName(),
            'description' => $publisher->getDescription(),
            'color' => $publisher->getColor(),
            'logo' => $publisher->getLogo()
        ];

        return $formatedPublisher;
    }

    public function getPublisherList($request): array
    {
        [$total, $publishers] = $this->publisherRE->list($request);

        if ($publishers === []) {
            return [
                'total' => $total,
                'data' => []
            ];
        }

        return [
            'total' => $total,
            'data' => $publishers
        ];
    }

    public function selector()
    {
        return $this->publisherRE->getSelector();
    }

    public function edit(int $id, $request): Publisher
    {
        $publisher = $this->publisherRE->findOrFail($id);
        $publisher = $this->publisherRE->writeFromRequest($request, $publisher);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $publisher;
    }

    public function create($request): Publisher
    {
        $publisher = $this->publisherRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $publisher;
    }

    public function delete(int $id): void
    {
        $publisher = $this->publisherRE->findOrFail($id);
        $this->publisherRE->remove($publisher);
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
