<?php

namespace App\Service;

use App\Entity\Author;
use App\Exception\NotFoundException;
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

    public function getAuthorById($id): array
    {
        $author = $this->authorRE->findOrFail($id);

        if ($author->getUser() == NULL || $author->getUser()->getId() == $this->tokenUserId()) {
            $formatedAuthor = [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'firstSurname' => $author->getFirstSurname(),
                'secondSurname' => $author->getSecondSurname(),
                'biography' => $author->getBiography(),
                'birthDate' => $author->getBirthDate(),
            ];
        } else {
            throw new NotFoundException('Autor no encontrado');
        }

        return $formatedAuthor;
    }

    public function getAuthorList($request): array
    {
        [$total, $authors] = $this->authorRE->list($request);

        if ($authors === []) {
            return [
                'total' => $total,
                'data' => []
            ];
        }

        return [
            'total' => $total,
            'data' => $authors
        ];
    }

    public function edit(int $id, $request): Author
    {
        $author = $this->authorRE->findOrFail($id);

        if ($author->getUser() == NULL || $author->getUser()->getId() != $this->tokenUserId()) {
            throw new NotFoundException('No tienes permisos para editar este autor');
        }
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
