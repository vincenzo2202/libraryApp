<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Purchase;
use App\Entity\User;
use App\Exception\NotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Utilities\RepositoryUtilities;

/**
 * @extends ServiceEntityRepository<Purchase>
 */
class PurchaseRepository extends ServiceEntityRepository
{
    private $_em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
        $this->_em = $registry->getManager();
    }

    public function findOrFail(int $id): Purchase
    {
        $purchase = $this->find($id);
        if (!$purchase) throw new NotFoundException("Purchase no encontrado");

        return $purchase;
    }

    public function getSelector(): array
    {
        $data = $this->createQueryBuilder('P')
            ->select('P.id') // Seleccionar los campos necesarios
            ->getQuery()
            ->getResult();

        return $data;
    }

    public function setPropertiesIfFound(Request $request, Purchase $purchase): Purchase
    {
        $request->get('quantity') === null ? '' : $purchase->setQuantity($request->get('quantity'));
        $request->get('purchasePrice') === null ? '' : $purchase->setPurchasePrice($request->get('purchasePrice'));
        $request->get('purchaseDate') === null ? '' : $purchase->setPurchaseDate($request->get('purchaseDate'));

        if ($request->get('user') !== null) {
            $userRepository = $this->_em->getRepository(User::class);
            $user = $userRepository->findOrFail($request->get('user'));
            $purchase->setUser($user);
        }
        if ($request->get('magazine') !== null) {
            $magazineRepository = new MagazineRepository($this->registry);
            $magazine = $magazineRepository->findOrFail($request->get('magazine'));
            $purchase->setMagazine($magazine);
        }
        if ($request->get('book') !== null) {
            $bookRepository = $this->_em->getRepository(Book::class);
            $book = $bookRepository->findOrFail($request->get('book'));
            $purchase->setBook($book);
        }

        return $purchase;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Purchase $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): Purchase
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof Purchase) {
            $purchase = $entityToEdit;
            $inCreationTime = false;
        } else {
            $purchase = new Purchase();
            $inCreationTime = true;
        }

        $purchase = $this->setPropertiesIfFound($request, $purchase, $inCreationTime);

        $this->_em->persist($purchase);

        return $purchase;
    }

    private function paginateQuery($data, $request)
    {
        $nPage = $request->get('nPage');
        $nReturns = $request->get('nReturns');

        $start = ($nPage - 1) * $nReturns;
        $paginatedData = array_slice($data, $start, $nReturns);
        $total = count($data);

        return [
            $total,
            $paginatedData
        ];
    }
}
