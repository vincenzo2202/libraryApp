<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Magazine;
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

    public function setPropertiesIfFound(Request $request, Purchase $purchase, $inCreationTime): Purchase
    {
        $request->get('quantity') === null ? '' : $purchase->setQuantity($request->get('quantity'));
        $request->get('purchasePrice') === null ? '' : $purchase->setPurchasePrice($request->get('purchasePrice'));
        $request->get('purchaseDate') === null ? '' : $purchase->setPurchaseDate($request->get('purchaseDate'));

        if ($request->get('user') !== null) {
            $userRepository = $this->_em->getRepository(User::class);
            $user = $userRepository->findOrFail($request->get('user'));
            $purchase->setUser($user);
        }
        if ($request->get('type') === 'magazine') {
            $magazineRepository = $this->_em->getRepository(Magazine::class);
            if ($inCreationTime === true) {
                $magazine = $magazineRepository->writeFromRequest($request);
                $purchase->setMagazine($magazine);
            } else {
                $magazine = $purchase->getMagazine();
                $magazine = $magazineRepository->setPropertiesIfFound($request, $magazine);
            }
        }
        if ($request->get('type') === 'book') {
            $bookRepository = $this->_em->getRepository(Book::class);
            if ($inCreationTime === true) {
                $book = $bookRepository->writeFromRequest($request);
                $purchase->setBook($book);
            } else {
                $book = $purchase->getBook();
                $book = $bookRepository->setPropertiesIfFound($request, $book);
            }
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

    public function list($request): array
    {
        $genericFilter = $request->get('genericFilter');
        $orderBy = $request->get('orderBy');

        $query = $this->createQueryBuilder('P')
            ->select('P.id', 'P.quantity', 'P.purchasePrice', 'P.purchaseDate')
            ->leftJoin('P.user', 'U')
            ->leftJoin('P.magazine', 'M')
            ->leftJoin('P.book', 'B')
            ->leftJoin('B.publisher', 'BP')
            ->leftJoin('M.publisher', 'MP')
            ->leftJoin('B.categories', 'BC')
            ->leftJoin('M.categories', 'MC')
            ->leftJoin('B.author', 'A')
            ->addSelect(
                'CONCAT(
                    \'{ "type": "\', 
                    CASE 
                        WHEN B.title IS NOT NULL THEN \'book\' 
                        ELSE \'magazine\' 
                    END,
                    \'", "title": "\', 
                    CASE 
                        WHEN B.title IS NOT NULL THEN B.title 
                        ELSE M.title 
                    END,
                    \'", "id": "\', 
                    CASE 
                        WHEN B.title IS NOT NULL THEN B.id 
                        ELSE M.id 
                    END,
                    \'", "status": "\', 
                    CASE 
                        WHEN B.title IS NOT NULL THEN B.status 
                        ELSE M.status 
                    END, 
                    \'", "publisher": { "name": "\', 
                    CASE 
                        WHEN BP.name IS NOT NULL THEN BP.name 
                        ELSE MP.name 
                    END,
                    \'", "id": "\',
                    CASE 
                        WHEN BP.id IS NOT NULL THEN BP.id 
                        ELSE MP.id 
                    END, 
                    \'"}, "categories": { "name": "\',
                    CASE 
                        WHEN BC.name IS NOT NULL THEN BC.name 
                        ELSE MC.name 
                    END,
                    \'", "id": "\',
                    CASE 
                        WHEN BC.id IS NOT NULL THEN BC.id 
                        ELSE MC.id 
                    END,     
                    \'"}, "author": { "name": "\',   
                    CASE 
                        WHEN A.name IS NOT NULL THEN CONCAT(A.name, \' \', A.firstSurname, \' \', A.secondSurname)
                        ELSE \'\'
                    END,
                    \'", "id": "\',
                    CASE 
                        WHEN A.id IS NOT NULL THEN A.id 
                        ELSE \'\'
                    END,
                    \'"}, "number": "\', 
                    CASE 
                        WHEN M.number IS NOT NULL THEN M.number  
                        ELSE \'\'
                    END,
                    \'"} \' 
                ) AS copy'
            )
            ->andWhere('U.id = :userId')
            ->setParameter('userId', $request->get('user'));

        if ($genericFilter !== null) {
            $query
                ->andWhere('P.quantity LIKE :genericFilter')
                ->orWhere('P.purchasePrice LIKE :genericFilter')
                ->orWhere('P.purchaseDate LIKE :genericFilter')
                ->orWhere('M.title LIKE :genericFilter')
                ->orWhere('B.title LIKE :genericFilter')
                // añadir las que sean necesarias para la busqueda
                ->setParameter('genericFilter', '%' . $genericFilter . '%');
        }

        // filtro por type
        $type = $request->get('type');
        if ($type === 'book') {
            $query->andWhere('B.title IS NOT NULL');
        } elseif ($type === 'magazine') {
            $query->andWhere('M.title IS NOT NULL');
        }

        // filtro por status
        $status = $request->get('status');
        if ($status !== null) {
            $query->andWhere('B.status = :status')
                ->setParameter('status', $status);
        }

        $orderBy = strtoupper($orderBy);
        if ($orderBy !== 'ASC' && $orderBy !== 'DESC') {
            $orderBy = 'DESC';
        }

        $query->orderBy('P.id', $orderBy);

        $data = $query->getQuery()->getResult();

        // Decodificar el campo 'copy' y  publisher de JSON a un objeto PHP
        $data = array_map(function ($item) {
            $item['copy'] = json_decode($item['copy'], true);
            return $item;
        }, $data);

        $data = array_map(function ($item) {
            $item['copy']['type'] = $item['copy']['type'] ?? null;
            if ($item['copy']['type'] === 'book') {
                $item['copy']['author'] = $item['copy']['author'] ?? null;
                unset($item['copy']['number']);
            } else {
                $item['copy']['number'] = $item['copy']['number'] ?? null;
                unset($item['copy']['author']);
            }
            return $item;
        }, $data);





        return $this->paginateQuery($data, $request);
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
