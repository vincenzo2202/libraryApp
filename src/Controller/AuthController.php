<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ImageUtilities;
use App\Service\UserManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

// #[Route('/api')]
class AuthController extends ApiController
{
    // register
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserManagerService $userManagerSE): Response
    {
        $request = $this->transformJsonBody($request);

        $this->allNeededParametersPresent($request);

        $userManagerSE->create($request);

        return $this->respondWithSuccess('Usuario creado correctamente');
    }


    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->findOneBy(array('username' => $user->getUserIdentifier()));
        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }


    private function allNeededParametersPresent($clienteJson): string
    {
        $parameters = ['name', 'firstSurname', 'username', 'password'];

        foreach ($parameters as $param) {
            if (
                $clienteJson->get($param) === null || $clienteJson->get($param) === ''
            ) {
                return $param;
            }

            if ($param !== '') {
                return $this->respondValidationError('Falta el parámetro: ' . $param);
            }
        }


        return '';
    }
}
