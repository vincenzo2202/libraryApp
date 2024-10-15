<?php
// App\Security\CanAccessAuthenticator

namespace App\Security;

use App\Exception\AccessDeniedException;
use App\Exception\CustomAuthenticationException;
use App\Exception\ValidationErrorException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as HasherUserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CanAccessAuthenticator extends AbstractAuthenticator
{
    private $successHandler;
    private $failureHandler;
    private $passwordHasher;
    private $userProvider;

    public function __construct(
        AuthenticationSuccessHandler $successHandler,
        AuthenticationFailureHandler $failureHandler,
        HasherUserPasswordHasherInterface $passwordHasher,
        UserProviderInterface $userProvider
    ) {
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
    }

    // El método supports determina si este authenticator debe manejar la solicitud
    public function supports(Request $request): bool
    {
        // Puedes añadir más lógica aquí para verificar el endpoint específico, por ejemplo:
        // return $request->attributes->get('_route') === 'login_route';
        return true; // Asume que siempre soporta la autenticación para simplificar
    }

    // Este método reemplaza a getCredentials() y getUser() para autenticar
    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            throw new CustomAuthenticationException('Datos de autenticación inválidos.');
        }

        $username = $data['username'];
        $password = $data['password'];

        // Usamos el UserBadge para cargar al usuario
        return new SelfValidatingPassport(
            new UserBadge($username, function ($username) use ($password) {
                // Cargar el usuario desde el proveedor de usuarios
                $user = $this->userProvider->loadUserByIdentifier($username);


                if (!$user) {
                    throw new UserNotFoundException('Usuario no encontrado.');
                }
                if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                    throw new CustomAuthenticationException('El usuario o la contraseña no son validos.');
                }

                if ($user->isBloqued()) {
                    throw new CustomAuthenticationException('Tu cuenta ha sido bloqueada.');
                }


                return $user;
            })
        );
    }

    // Valida las credenciales, este método ahora verifica las contraseñas
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $credentials['password']);
    }

    // Si la autenticación falla
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    // Si la autenticación es exitosa
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //comprobar que el usuario está activo
        if ($token->getUser()->isBloqued()) {
            return new JsonResponse(['status' => 403, 'message' => 'No tienes permisos para acceder a este recurso. Por favor, contacta con el administrador si crees que esto es un error.'], 403);
        }
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    // Si falta la autenticación, el método start es llamado
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(['status' => 401, 'message' => 'Se requiere autenticación'], 401);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
