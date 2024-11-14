<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class BookManagerService
{
    private $security;

    public function __construct(
        private BookRepository $bookRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function selector()
    {
        return $this->bookRE->getSelector();
    }

    public function edit(int $id, $request): Book
    {
        $book = $this->bookRE->findOrFail($id);
        $book = $this->bookRE->writeFromRequest($request, $book);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $book;
    }

    public function create(Request $request): Book
    {
        $book = $this->bookRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $book;
    }

    public function delete(int $id): void
    {
        $book = $this->bookRE->findOrFail($id);
        $this->bookRE->remove($book);
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
