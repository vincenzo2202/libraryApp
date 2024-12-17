<?php

namespace App\Service;

use App\Entity\Book;
use App\Exception\NotFoundException;
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

    public function getBooksById($id): array
    {
        $book = $this->bookRE->findOrFail($id);

        if ($book->getUser() == NULL || $book->getUser()->getId() == $this->tokenUserId()) {
            $formatedBook = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'isSpecialEdition' => $book->isSpecialEdition(),
                'status' => $book->getStatus()->value,
                'author' => [
                    'id' => $book->getAuthor()->getId(),
                    'name' => $book->getAuthor()->getName() . ' ' . $book->getAuthor()->getFirstSurname() . ' ' . $book->getAuthor()->getSecondSurname(),
                    'biography' => $book->getAuthor()->getBiography(),
                    'birthDate' => $book->getAuthor()->getBirthDate(),
                ],
                'publisher' => [
                    'id' => $book->getPublisher()->getId(),
                    'name' => $book->getPublisher()->getName(),
                ],
                'categories' => $book->getCategories()->map(function ($category) {
                    return [
                        'id' => $category->getId(),
                        'name' => $category->getName(),
                    ];
                })->toArray(),
            ];
        } else {
            throw new NotFoundException('Libro no encontrado');
        }

        return $formatedBook;
    }

    public function getBooksList($request): array
    {
        [$total, $books] = $this->bookRE->list($request);

        if ($books === []) {
            return [
                'total' => $total,
                'data' => []
            ];
        }

        return [
            'total' => $total,
            'data' => $books
        ];
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

    public function delete(array $ids): void
    {
        $toDelete = [];


        foreach ($ids as $id) {
            $book = $this->bookRE->findOrFail($id);
            if ($book->getUser() == NULL || $book->getUser()->getId() !== $this->tokenUserId()) {
                continue;
            }
            $toDelete[] = $book;
        }

        foreach ($toDelete as $book) {
            $this->bookRE->remove($book, false);
        }

        $this->em->flush();
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
