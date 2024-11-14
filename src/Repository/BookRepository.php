<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Publisher;
use App\Exception\NotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Utilities\RepositoryUtilities;
use App\Service\ImageUtilities;
use App\Enum\Status;
use App\Exception\ValidationErrorException;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{

    private $imageUtilities;
    private $_em;

    public function __construct(ManagerRegistry $registry, ImageUtilities $imageUtilities)
    {
        parent::__construct($registry, Book::class);
        $this->_em = $registry->getManager();
        $this->imageUtilities = $imageUtilities;
    }

    public function findOrFail(int $id): Book
    {
        $book = $this->find($id);
        if (!$book) throw new NotFoundException("Book no encontrado");

        return $book;
    }

    public function getSelector(): array
    {
        $data = $this->createQueryBuilder('B')
            ->select('B.id') // Seleccionar los campos necesarios
            ->getQuery()
            ->getResult();

        return $data;
    }

    public function setPropertiesIfFound(Request $request, Book $book): Book
    {
        $request->get('title') === null ? '' : $book->setTitle($request->get('title'));
        $request->get('isbn') === null ? '' : $book->setIsbn($request->get('isbn'));
        $request->get('description') === null ? '' : $book->setDescription($request->get('description'));
        $request->get('publicationYear') === null ? '' : $book->setPublicationYear($request->get('publicationYear'));
        $request->get('pages') === null ? '' : $book->setPages($request->get('pages'));
        $request->get('comment') === null ? '' : $book->setComment($request->get('comment'));
        $request->get('isSpecialEdition') === null ? '' : $book->setSpecialEdition($request->get('isSpecialEdition'));

        $imageFile = $request->files->get('coverImage');

        if ($imageFile) {
            $imagePath = $this->imageUtilities->uploadImage($imageFile);
            $book->setCoverImage($imagePath);
        }

        if ($request->get('status') !== null) {
            $statusValue = $request->get('status');
            if (!Status::isValid($statusValue)) {
                throw new ValidationErrorException("El estado `$statusValue` no es válido");
            }
            $status = Status::from($statusValue);
            $book->setStatus($status);
        }

        if ($request->get('publisher') !== null && gettype($request->get('publisher')) ===  'object') {
            // creo un nuevo publisher
        } else {
            $publisherRepository = $this->_em->getRepository(Publisher::class);
            $publisher = $publisherRepository->findOrFail($request->get('publisher'));

            $book->setPublisher($publisher);
        }

        if ($request->get('categories') !== null && gettype($request->get('categories')) ===  'object') {
            // creo un nuevo category
        } else {

            $categoryRepository = $this->_em->getRepository(Category::class);
            $category = $categoryRepository->findOrFail($request->get('categories'));
            $book->addCategory($category);
        }
        if ($request->get('author') !== null && gettype($request->get('author')) ===  'object') {
            // creo un nuevo author
        } else {
            $authorRepository = $this->_em->getRepository(Author::class);
            $author = $authorRepository->findOrFail($request->get('author'));
            $book->setAuthor($author);
        }

        return $book;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Book $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): Book
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof Book) {
            $book = $entityToEdit;
            $inCreationTime = false;
        } else {
            $book = new Book();
            $inCreationTime = true;
        }

        $book = $this->setPropertiesIfFound($request, $book, $inCreationTime);

        $this->_em->persist($book);

        return $book;
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
