<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserManagerService
{
    public function __construct(
        private UserRepository $userRE,
        private EntityManagerInterface $em
    ) {}

    public function selector()
    {
        return $this->userRE->getSelector();
    }

    public function edit(int $id, $request): User
    {
        $user = $this->userRE->findOrFail($id);
        $user = $this->userRE->writeFromRequest($request, $user);

        return $user;
    }

    public function create(Request $request): User
    {
        $user = $this->userRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $user;
    }

    public function delete(int $id): void
    {
        $user = $this->userRE->findOrFail($id);
        $this->userRE->remove($user);
    }
}
