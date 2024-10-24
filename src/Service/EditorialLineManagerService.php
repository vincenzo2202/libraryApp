<?php

namespace App\Service;

use App\Entity\EditorialLine;
use App\Exception\NotFoundException;
use App\Repository\EditorialLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class EditorialLineManagerService
{
    private $security;

    public function __construct(
        private EditorialLineRepository $editorialLineRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function getEditorialLineById($id): array
    {
        $editorialLine = $this->editorialLineRE->findOrFail($id);
        // TODO: todas las lineas editoriales deben tener un publisher?
        // $me = $this->tokenUserId();
        // if ($editorialLine->getPublisher()->getUser()->getId() !== $me) {
        //     throw new NotFoundException('Editorial no encontrado');
        // }

        $formatedEditorialLine = [
            'id' => $editorialLine->getId(),
            'name' => $editorialLine->getName(),
            'description' => $editorialLine->getDescription(),
            'color' => $editorialLine->getColor(),
            'coverImage' => $editorialLine->getCoverImage()
        ];

        return $formatedEditorialLine;
    }

    public function getEditorialLineList($request): array
    {
        [$total, $editorialLines] = $this->editorialLineRE->list($request);

        if ($editorialLines === []) {
            return [
                'total' => $total,
                'data' => []
            ];
        }

        return [
            'total' => $total,
            'data' => $editorialLines
        ];
    }

    public function selector()
    {
        return $this->editorialLineRE->getSelector();
    }
    // 
    public function edit(int $id, $request): EditorialLine
    {
        $editorialLine = $this->editorialLineRE->findOrFail($id);
        $editorialLine = $this->editorialLineRE->writeFromRequest($request, $editorialLine);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $editorialLine;
    }

    public function create(Request $request): EditorialLine
    {
        $editorialLine = $this->editorialLineRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $editorialLine;
    }

    public function delete(array $ids): void
    {
        $toDelete = [];
        foreach ($ids as $id) {
            $editorialLine = $this->editorialLineRE->findOrFail($id);
            $toDelete[] = $editorialLine;
        }

        foreach ($toDelete as $editorialLine) {
            $this->editorialLineRE->remove($editorialLine);
        }
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
