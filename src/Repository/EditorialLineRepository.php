<?php

namespace App\Repository;

use App\Entity\EditorialLine;
use App\Entity\Publisher;
use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Utilities\RepositoryUtilities;
use App\Service\ImageUtilities;

/**
 * @extends ServiceEntityRepository<EditorialLine>
 */
class EditorialLineRepository extends ServiceEntityRepository
{
    private $_em;
    private ImageUtilities $imageUtilities;

    public function __construct(ManagerRegistry $registry, ImageUtilities $imageUtilities)
    {
        parent::__construct($registry, EditorialLine::class);
        $this->_em = $registry->getManager();
        $this->imageUtilities = $imageUtilities;
    }

    public function findOrFail(int $id): EditorialLine
    {
        $editorialLine = $this->find($id);
        if (!$editorialLine) throw new NotFoundException("EditorialLine no encontrado");

        return $editorialLine;
    }

    public function getSelector(): array
    {
        $data = $this->createQueryBuilder('E')
            ->select('E.id', 'E.name')
            ->getQuery()
            ->getResult();

        return $data;
    }

    public function setPropertiesIfFound(Request $request, EditorialLine $editorialLine): EditorialLine
    {
        $name = $request->get('name');
        if ($name !== null && strlen($name) > 255) {
            throw new ValidationErrorException(' El nombre no puede tener más de 255 caracteres.');
        }
        $name === null ? '' : $editorialLine->setName($name);

        $description = $request->get('description');
        if ($description !== null && strlen($description) > 255) {
            throw new ValidationErrorException('La descripción no puede tener más de 255 caracteres.');
        }
        $editorialLine === null ? '' : $editorialLine->setDescription($description);

        $request->get('color') === null ? '' : $editorialLine->setColor($request->get('color'));

        if ($request->files->get('coverImage') !== null) {
            $imageFile = $request->files->get('coverImage');
            if ($imageFile) {
                $imagePath = $this->imageUtilities->uploadImage($imageFile);
                $editorialLine->setCoverImage($imagePath);
            }
        }

        if ($request->get('publisher') !== null) {
            $publisherRepository = $this->_em->getRepository(Publisher::class);
            $publisher = $publisherRepository->findOrFail($request->get('publisher'));
            if (!$publisher) {
                throw new NotFoundException("Publisher no encontrado");
            }
            $editorialLine->setPublisher($publisher);
        }

        return $editorialLine;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(EditorialLine $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): EditorialLine
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof EditorialLine) {
            $editorialLine = $entityToEdit;
            $inCreationTime = false;
        } else {
            $editorialLine = new EditorialLine();
            $inCreationTime = true;
        }

        $editorialLine = $this->setPropertiesIfFound($request, $editorialLine, $inCreationTime);

        $this->_em->persist($editorialLine);

        return $editorialLine;
    }

    public function list($request): array
    {
        $genericFilter = $request->get('genericFilter');
        $orderBy = $request->get('orderBy');

        $query  = $this->createQueryBuilder('E')
            ->select('E.id', 'E.name', 'E.description', 'E.color', 'E.coverImage', 'P.name as publisher', 'P.id as publisherId')
            ->leftJoin('E.publisher', 'P');

        if ($genericFilter) {
            $query->andWhere('E.name LIKE :genericFilter')
                ->setParameter('genericFilter', '%' . $genericFilter . '%');
        }

        $orderBy = strtoupper($orderBy);
        if ($orderBy !== 'ASC' && $orderBy !== 'DESC') {
            $orderBy = 'DESC';
        }

        $query->orderBy('E.id', $orderBy);

        $data = $query->getQuery()->getResult();
        $data = $this->formatData($data);
        $dataPaginated = $this->paginateQuery($data, $request);

        return $dataPaginated;
    }

    // TODO: sacar a un format service
    private function formatData($data)
    {
        $formatedData = [];
        foreach ($data as $editorialLine) {
            $formatedData[] = [
                'id' => $editorialLine['id'],
                'name' => $editorialLine['name'],
                'description' => $editorialLine['description'],
                'color' => $editorialLine['color'],
                'coverImage' => $editorialLine['coverImage'],
                'publisher' => [
                    'id' => $editorialLine['publisherId'],
                    'name' => $editorialLine['publisher']
                ]
            ];
        }

        return $formatedData;
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
