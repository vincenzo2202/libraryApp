<?php

namespace App\Repository;

use App\Entity\EditorialLine;
use App\Entity\Publisher;
use App\Exception\NotFoundException;
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

    // public function getSelector(): array
    // {
    //     // TODO: Implement getSelector() method.
    // }

    public function setPropertiesIfFound(Request $request, EditorialLine $editorialLine): EditorialLine
    {
        $request->get('name') === null ? '' : $editorialLine->setName($request->get('name'));
        $request->get('description') === null ? '' : $editorialLine->setDescription($request->get('description'));
        $request->get('color') === null ? '' : $editorialLine->setColor($request->get('color'));

        if ($request->files->get('coverImage') !== null) {
            $imageFile = $request->files->get('coverImage');
            if ($imageFile) {
                $imagePath = $this->imageUtilities->uploadImage($imageFile);
                $editorialLine->setCoverImage($imagePath);
            }
        }
        // lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua

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
