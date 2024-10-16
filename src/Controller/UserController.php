<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class UserController extends ApiController
{
    #[Route('/user', name: 'api_users', methods: ['GET'])]
    // #[IsGranted('ROLE_USER')]
    public function getUserList(Request $request, UserManagerService $userManagerSE): Response
    {
        $request = $this->transformJsonBody($request);
        $users = $userManagerSE->getList($request);

        return $this->response($users);
    }
}
