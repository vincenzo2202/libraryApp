<?php

namespace App\Repository;

use App\Entity\Publisher;
use App\Entity\User;
use App\Exception\NotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Utilities\RepositoryUtilities;
use App\Service\ImageUtilities;

/**
 * @extends ServiceEntityRepository<Publisher>
 */
class PublisherRepository extends ServiceEntityRepository
{
    private $_em;
    private ImageUtilities $imageUtilities;

    public function __construct(ManagerRegistry $registry, ImageUtilities $imageUtilities)
    {
        parent::__construct($registry, Publisher::class);
        $this->_em = $registry->getManager();
        $this->imageUtilities = $imageUtilities;
    }

    public function findOrFail(int $id): Publisher
    {
        $publisher = $this->find($id);
        if (!$publisher) throw new NotFoundException("Publisher no encontrado");

        return $publisher;
    }

    // public function getSelector(): array
    // {
    //     // TODO: Implement getSelector() method.
    // }

    public function setPropertiesIfFound(Request $request, Publisher $publisher): Publisher
    {

        $request->get('name') === null ? '' : $publisher->setName($request->get('name'));
        $request->get('description') === null ? '' : $publisher->setDescription($request->get('description'));
        $request->get('color') === null ? '' : $publisher->setColor($request->get('color'));

        if ($request->files->get('logo') !== null) {
            $imageFile = $request->files->get('logo');
            if ($imageFile) {
                $imagePath = $this->imageUtilities->uploadImage($imageFile);
                $publisher->setLogo($imagePath);
            }
        }

        if ($request->get('user') !== null) {
            $user = $request->get('user');
            $userRepository = $this->_em->getRepository(User::class);
            $user = $userRepository->findOrFail($user);
            $publisher->setUser($user);
        }

        return $publisher;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Publisher $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): Publisher
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof Publisher) {

            $publisher = $entityToEdit;
            $inCreationTime = false;
        } else {
            $publisher = new Publisher();
            $inCreationTime = true;
        }

        $publisher = $this->setPropertiesIfFound($request, $publisher, $inCreationTime);
        $this->_em->persist($publisher);

        return $publisher;
    }

    public function list($request): array
    {
        $genericFilter = $request->get('genericFilter');
        $orderBy = $request->get('orderBy');

        $nPage = $request->get('nPage');
        $nReturns = $request->get('nReturns');

        $query = $this->createQueryBuilder('P')
            ->select('P.id', 'P.name', 'P.description', 'P.color', 'P.logo')
            ->leftJoin('P.user', 'U')
            ->andWhere('U.id = :user')
            ->setParameter('user', $request->get('user'));

        if ($genericFilter) {
            $query->andWhere('P.name LIKE :genericFilter')
                ->orWhere('P.description LIKE :genericFilter')
                ->orWhere('P.color LIKE :genericFilter')
                ->setParameter('genericFilter', '%' . $genericFilter . '%');
        }

        $orderBy = strtoupper($orderBy);
        if ($orderBy !== 'ASC' && $orderBy !== 'DESC') {
            $orderBy = 'DESC';
        }

        $query->orderBy('P.id', $orderBy);

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
