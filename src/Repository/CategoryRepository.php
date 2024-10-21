<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use App\Exception\NotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Utilities\RepositoryUtilities;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    private $_em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
        $this->_em = $registry->getManager();
    }

    public function findOrFail(int $id): Category
    {
        $category = $this->find($id);
        if (!$category) throw new NotFoundException("Category no encontrado");

        return $category;
    }

    public function setPropertiesIfFound(Request $request, Category $category): Category
    {
        $request->get('name') === null ? '' : $category->setName($request->get('name'));
        $request->get('color') === null ? '' : $category->setColor($request->get('color'));

        if ($request->get('user') !== null) {
            $userId = $request->get('user');
            $userRepository = $this->_em->getRepository(User::class);
            $user = $userRepository->findOrFail($userId);
            $category->setUser($user);
        }

        return $category;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Category $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): Category
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof Category) {
            $category = $entityToEdit;
            $inCreationTime = false;
        } else {
            $category = new Category();
            $inCreationTime = true;
        }

        $category = $this->setPropertiesIfFound($request, $category, $inCreationTime);

        $this->_em->persist($category);

        return $category;
    }

    public function list($request): array
    {
        $genericFilter = $request->get('genericFilter');
        $orderBy = $request->get('orderBy');

        $query = $this->createQueryBuilder('C')
            ->select('C.id', 'C.name', 'C.color')
            ->leftJoin('C.user', 'U')
            ->andWhere('U.id = :user')
            ->setParameter('user', $request->get('user'));

        if ($genericFilter !== null) {
            $query->andWhere('C.name LIKE :genericFilter')
                ->setParameter('genericFilter', '%' . $genericFilter . '%');
        }

        if (strtoupper($orderBy) === 'ASC') {
            $query->orderBy('C.id', 'ASC');
        } else if (strtoupper($orderBy) === 'DESC') {
            $query->orderBy('C.id', 'DESC');
        } else {
            $query->orderBy('C.id', 'DESC');
        }

        $data = $query->getQuery()->getResult();
        $dataPaginated = $this->paginateQuery($data, $request);

        return $dataPaginated;
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
