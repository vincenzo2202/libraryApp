<?php

namespace App\Service;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class AuthorManagerService
{
    private $security;

    public function __construct(
        private AuthorRepository $authorRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function selector()
    {
        return $this->authorRE->getSelector();
    }

    public function edit(int $id, $request): Author
    {
        $author = $this->authorRE->findOrFail($id);
        $author = $this->authorRE->writeFromRequest($request, $author);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $author;
    }

    public function create(Request $request): Author
    {
        $author = $this->authorRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $author;
    }

    public function delete(int $id): void
    {
        $author = $this->authorRE->findOrFail($id);
        $this->authorRE->remove($author);
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
