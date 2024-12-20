<?php

namespace App\Service;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class PurchaseBusinessService
{
    private $security;

    public function __construct(
        private PurchaseRepository $purchaseRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function getPurchaseStatistics($userId)
    {
        $balance = $this->purchaseRE->purchaseStatistics($userId);

        return $balance;
    }
}
