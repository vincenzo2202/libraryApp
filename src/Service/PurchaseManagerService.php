<?php

namespace App\Service;

use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class PurchaseManagerService
{
    private $security;

    public function __construct(
        private PurchaseRepository $purchaseRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function selector()
    {
        return $this->purchaseRE->getSelector();
    }

    public function edit(int $id, $request): Purchase
    {
        $purchase = $this->purchaseRE->findOrFail($id);
        $purchase = $this->purchaseRE->writeFromRequest($request, $purchase);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $purchase;
    }

    public function create(Request $request): Purchase
    {
        $purchase = $this->purchaseRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $purchase;
    }

    public function delete(int $id): void
    {
        $purchase = $this->purchaseRE->findOrFail($id);
        $this->purchaseRE->remove($purchase);
    }

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
