<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Magazine;
use App\Entity\Purchase;
use App\Exception\NotFoundException;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class PurchaseManagerService
{
    private $security;

    public function __construct(
        private PurchaseRepository $purchaseRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function selector()
    {
        return $this->purchaseRE->getSelector();
    }

    public function getPurchaseById($id): array
    {
        $purchase = $this->purchaseRE->findOrFail($id);

        if ($purchase->getUser() !== null && $purchase->getUser()->getId() === $this->tokenUserId()) {
            $formatedPurchase = [
                'id' => $purchase->getId(),
                'quantity' => $purchase->getQuantity(),
                'purchasePrice' => $purchase->getPurchasePrice(),
                'purchaseDate' => $purchase->getPurchaseDate(),
                'type' => $purchase->getMagazine() ? 'magazine' : 'book',
                'data' => $purchase->getMagazine() ? [
                    'id' => $purchase->getMagazine()->getId(),
                    'title' => $purchase->getMagazine()->getTitle(),
                    'issn' => $purchase->getMagazine()->getIssn(),
                    'description' => $purchase->getMagazine()->getDescription(),
                    'publicationYear' => $purchase->getMagazine()->getEditionYear(),
                    'editionMonth' => $purchase->getMagazine()->getEditionMonth(),
                    'coverImage' => $purchase->getMagazine()->getCoverImage(),
                    'number' => $purchase->getMagazine()->getNumber(),
                ] : [
                    'id' => $purchase->getBook()->getId(),
                    'title' => $purchase->getBook()->getTitle(),
                    'isbn' => $purchase->getBook()->getIsbn(),
                    'description' => $purchase->getBook()->getDescription(),
                    'publicationYear' => $purchase->getBook()->getPublicationYear(),
                    'coverImage' => $purchase->getBook()->getCoverImage(),
                    'author' => [
                        'id' => $purchase->getBook()->getAuthor()->getId(),
                        'name' => $purchase->getBook()->getAuthor()->getName(),
                        'firstSurname' => $purchase->getBook()->getAuthor()->getFirstSurname(),
                        'secondSurname' => $purchase->getBook()->getAuthor()->getSecondSurname(),
                        'biography' => $purchase->getBook()->getAuthor()->getBiography(),
                        'birthDate' => $purchase->getBook()->getAuthor()->getBirthDate(),
                    ],
                    'publisher' => [
                        'id' => $purchase->getBook()->getPublisher()->getId(),
                        'name' => $purchase->getBook()->getPublisher()->getName(),
                    ],
                    'categories' => $purchase->getBook()->getCategories()->map(function ($category) {
                        return [
                            'id' => $category->getId(),
                            'name' => $category->getName(),
                        ];
                    })->toArray(),
                ],
            ];
        } else {
            throw new NotFoundException('Compra no encontrada');
        }

        return $formatedPurchase;
    }

    public function edit(int $id, $request): Purchase
    {
        $purchase = $this->purchaseRE->findOrFail($id);
        if ($purchase->getBook() !== null) {
            $request->request->add(['type' => 'book']);
        } else {
            $request->request->add(['type' => 'magazine']);
        }

        $purchase = $this->purchaseRE->writeFromRequest($request, $purchase);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $purchase;
    }

    public function getPurchasesList($request): array
    {
        [$total, $purchases] = $this->purchaseRE->list($request);

        if ($purchases === []) {
            return [
                'total' => $total,
                'data' => []
            ];
        }

        return [
            'total' => $total,
            'data' => $purchases
        ];
    }

    public function create(Request $request): Purchase
    {
        $purchase = $this->purchaseRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $purchase;
    }

    public function delete(array $ids): void
    {
        $toDelete = [];
        $bookRepository = $this->em->getRepository(Book::class);
        $magazineRepository = $this->em->getRepository(Magazine::class);


        foreach ($ids as $id) {
            $purchase = $this->purchaseRE->findOrFail($id);
            if ($purchase->getUser() == null || $purchase->getUser()->getId() !== $this->tokenUserId()) {
                continue;
            }
            $toDelete[] = $purchase;
        }

        foreach ($toDelete as $purchase) {
            if ($purchase->getBook() !== null) {
                $bookRepository->remove($purchase->getBook(), false);
            } elseif ($purchase->getMagazine() !== null) {
                $magazineRepository->remove($purchase->getMagazine(), false);
            }
            $this->purchaseRE->remove($purchase, false);
        }

        $this->em->flush();
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
