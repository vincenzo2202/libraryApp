<?php

namespace App\Repository;

use App\Entity\Author;
use App\Exception\NotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Utilities\RepositoryUtilities;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    private $_em;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
        $this->_em = $registry->getManager();
    }

    public function findOrFail(int $id): Author
    {
        $author = $this->find($id);
        if (!$author) throw new NotFoundException("Author no encontrado");

        return $author;
    }

    public function getSelector(): array
    {
        $data = $this->createQueryBuilder('A')
            ->select('A.id', 'A.name') // Seleccionar los campos necesarios
            ->getQuery()
            ->getResult();

        return $data;
    }

    public function setPropertiesIfFound(Request $request, Author $author): Author
    {
        $request->get('name') === null ? '' : $author->setName($request->get('name'));
        $request->get('firstSurname') === null ? '' : $author->setFirstSurname($request->get('firstSurname'));
        $request->get('secondSurname') === null ? '' : $author->setSecondSurname($request->get('secondSurname'));
        $request->get('biography') === null ? '' : $author->setBiography($request->get('biography'));
        $request->get('birthDate') === null ? '' : $author->setBirthDate(
            new \DateTime($request->get('birthDate'))
        );

        return $author;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Author $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): Author
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof Author) {
            $author = $entityToEdit;
            $inCreationTime = false;
        } else {
            $author = new Author();
            $inCreationTime = true;
        }

        $author = $this->setPropertiesIfFound($request, $author, $inCreationTime);

        $this->_em->persist($author);

        return $author;
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
