<?php

namespace App\Repository;

use App\Controller\ApiController;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Service\ImageUtilities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Utilities\RepositoryUtilities;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private $passwordHasher;
    private $imageUtilities;
    private $_em;

    public function __construct(
        ManagerRegistry $registry,
        UserPasswordHasherInterface $passwordHasher,
        ImageUtilities $imageUtilities
    ) {
        parent::__construct($registry, User::class);
        $this->passwordHasher = $passwordHasher;
        $this->imageUtilities = $imageUtilities;
        $this->_em = $registry->getManager();
    }

    public function findOrFail(int $id): User
    {
        $user = $this->find($id);
        if (!$user) throw new NotFoundException("User no encontrado");

        return $user;
    }

    // public function getSelector(): array
    // {
    //     // TODO: Implement getSelector() method.
    // }

    public function setPropertiesIfFound(Request $request, User $user): User
    {
        $user->setUsername($request->get('username'));
        $user->setName($request->get('name'));
        $user->setFirstSurname($request->get('firstSurname'));
        $user->setSecondSurname($request->get('secondSurname'));
        $user->setCreationDate(date('Y-m-d H:i:s'));
        $user->setValidated(true);
        $user->setDeleted(false);
        $password = $this->passwordHasher->hashPassword($user, $request->get('password'));
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);

        // uso el imageUtilities para subir la imagen
        $imageFile = $request->files->get('profile'); // Asegúrate de que el campo en el formulario sea 'profile'

        if ($imageFile) {
            // Llama al método uploadImage del servicio ImageUtilities
            $imagePath = $this->imageUtilities->uploadImage($imageFile);
            $user->setProfileImage($imagePath); // Suponiendo que setProfile espera una ruta o URL
        }

        return $user;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function writeFromRequest($request, $entityToEdit = null): User
    {
        $request = RepositoryUtilities::arrayToRequest($request);

        if ($entityToEdit instanceof User) {
            $user = $entityToEdit;
            $inCreationTime = false;
        } else {
            $user = new User();
            $inCreationTime = true;
        }

        $user = $this->setPropertiesIfFound($request, $user, $inCreationTime);

        $this->_em->persist($user);

        return $user;
    }

    public function list($request)
    {
        $genericFilter = $request->get('genericFilter');
        $orderBy = $request->get('orderBy');

        $nPage = $request->get('nPage');
        $nReturns = $request->get('nReturns');

        $query = $this->obtainUserQuery();

        if ($genericFilter) {
            $query->andWhere('U.name LIKE :genericFilter')
                ->orWhere('U.firstSurname LIKE :genericFilter')
                ->orWhere('U.secondSurname LIKE :genericFilter')
                ->orWhere('U.username LIKE :genericFilter')
                ->setParameter('genericFilter', '%' . $genericFilter . '%');
        }

        $orderBy = strtoupper($orderBy);
        if ($orderBy !== 'ASC' && $orderBy !== 'DESC') {
            $orderBy = 'DESC';
        }
        $query->orderBy('U.id', $orderBy);

        $data = $query->getQuery()->getResult();

        $dataPaginated = $this->paginateQuery($data, $request);

        return $dataPaginated;
    }

    public function obtainUserQuery()
    {
        $query = $this->createQueryBuilder('U')
            ->select('U.id', 'U.username', 'U.name', 'U.firstSurname', 'U.secondSurname', 'U.profileImage', 'U.creationDate')
            ->andWhere('U.isDeleted = false')
            ->andWhere('U.isValidated = true');

        return $query;
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
