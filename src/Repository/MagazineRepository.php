<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\EditorialLine;
use App\Entity\Magazine;
use App\Entity\Publisher;
use App\Enum\Status;
use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use App\Service\ImageUtilities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Utilities\RepositoryUtilities;

/**
 * @extends ServiceEntityRepository<Magazine>
 */
class MagazineRepository extends ServiceEntityRepository
{

    private $imageUtilities;
    private $_em;

    public function __construct(ManagerRegistry $registry, ImageUtilities $imageUtilities)
    {
        parent::__construct($registry, Magazine::class);
        $this->_em = $registry->getManager();
        $this->imageUtilities = $imageUtilities;
    }

    public function findOrFail(int $id): Magazine
    {
        $magazine = $this->find($id);
        if (!$magazine) throw new NotFoundException("Magazine no encontrado");

        return $magazine;
    }

    public function getSelector(): array
    {
        $data = $this->createQueryBuilder('M')
            ->select('M.id') // Seleccionar los campos necesarios
            ->getQuery()
            ->getResult();

        return $data;
    }

    public function setPropertiesIfFound(Request $request, Magazine $magazine): Magazine
    {
        $request->get('title') === null ? '' : $magazine->setTitle($request->get('title'));
        $request->get('issn') === null ? '' : $magazine->setIssn($request->get('issn'));
        $request->get('description') === null ? '' : $magazine->setDescription($request->get('description'));
        $request->get('publicationYear') === null ? '' : $magazine->setEditionYear($request->get('publicationYear'));
        $request->get('editionMonth') === null ? '' : $magazine->setEditionMonth($request->get('editionMonth'));
        $request->get('coverImage') === null ? '' : $magazine->setCoverImage($request->get('coverImage'));
        $request->get('number') === null ? '' : $magazine->setNumber($request->get('number'));
        $request->get('comment') === null ? '' : $magazine->setComment($request->get('comment'));
        $request->get('isSpecialEdition') === null ? '' : $magazine->setSpecialEdition($request->get('isSpecialEdition'));

        $imageFile = $request->files->get('coverImage');

        if ($imageFile) {
            $imagePath = $this->imageUtilities->uploadImage($imageFile);
            $magazine->setCoverImage($imagePath);
        }

        if ($request->get('publisher') !== null) {
            $publisherRepository = $this->_em->getRepository(Publisher::class);
            $publisher = $publisherRepository->findOrFail($request->get('publisher'));
            $magazine->setPublisher($publisher);
        } else {
            '';
        }

        if ($request->get('editorialLine') !== null) {
            $editorialLineRepository = $this->_em->getRepository(EditorialLine::class);
            $editorialLine = $editorialLineRepository->findOrFail($request->get('editorialLine'));
            $magazine->setEditorialLine($editorialLine);
        } else {
            '';
        }

        if ($request->get('status') !== null) {
            $statusValue = $request->get('status');
            if (!Status::isValid($statusValue)) {
                throw new ValidationErrorException("El estado `$statusValue` no es válido");
            }
            $status = Status::from($statusValue);
            $magazine->setStatus($status);
        }

        if ($request->get('categories') !== null && gettype($request->get('categories')) ===  'array') {
            $magazine->removeAllCategories();
            foreach ($request->get('categories') as $categoryId) {
                $categoryRepository = $this->_em->getRepository(Category::class);
                $category = $categoryRepository->findOrFail($categoryId);
                $magazine->addCategory($category);
            }
        } else {
            '';
        }

        return $magazine;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Magazine $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): Magazine
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof Magazine) {
            $magazine = $entityToEdit;
            $inCreationTime = false;
        } else {
            $magazine = new Magazine();
            $inCreationTime = true;
        }

        $magazine = $this->setPropertiesIfFound($request, $magazine, $inCreationTime);

        $this->_em->persist($magazine);

        return $magazine;
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
