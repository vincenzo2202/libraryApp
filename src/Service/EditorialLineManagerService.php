<?php

namespace App\Service;

use App\Entity\EditorialLine;
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

    public function selector()
    {
        return $this->editorialLineRE->getSelector();
    }

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

    public function delete(int $id): void
    {
        $editorialLine = $this->editorialLineRE->findOrFail($id);
        $this->editorialLineRE->remove($editorialLine);
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
