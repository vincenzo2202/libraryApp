<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ImageUtilities;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mime\MimeTypes;

// #[Route('/api')]
class AuthController extends ApiController
{
    // register
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, ImageUtilities $imageUtilities): Response
    {
        $request = $this->transformJsonBody($request);

        $user = new User();
        $user->setUsername($request->get('username'));
        $user->setName($request->get('name'));
        $user->setFirstSurname($request->get('firstSurname'));
        $user->setSecondSurname($request->get('secondSurname'));
        $user->setCreationDate(date('Y-m-d H:i:s'));
        $user->setValidated(true);
        $user->setDeleted(false);
        $password = $passwordHasher->hashPassword($user, $request->get('password'));
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);

        // uso el imageUtilities para subir la imagen
        $imageFile = $request->files->get('profile'); // Asegúrate de que el campo en el formulario sea 'profile'

        if ($imageFile) {
            // Llama al método uploadImage del servicio ImageUtilities
            $imagePath = $imageUtilities->uploadImage($imageFile);
            $user->setProfileImage($imagePath); // Suponiendo que setProfile espera una ruta o URL
        }

        $em->persist($user);
        $em->flush();

        return $this->respondWithSuccess('Usuario creado correctamente');
    }


    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->findOneBy(array('username' => $user->getUserIdentifier()));
        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }
}
