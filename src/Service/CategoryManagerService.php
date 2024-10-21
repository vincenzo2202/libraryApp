<?php

namespace App\Service;

use App\Entity\Category;
use App\Exception\NotFoundException;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class CategoryManagerService
{
    public function __construct(
        private CategoryRepository $categoryRE,
        private EntityManagerInterface $em
    ) {}

    public function getCategoryList(Request $request): array
    {
        [$total, $categories] = $this->categoryRE->list($request);

        if ($categories === []) {
            return [
                'total' => $total,
                'data' => []
            ];
        }

        return [
            'total' => $total,
            'data' => $categories
        ];
    }
    public function edit(int $id, $request): Category
    {
        $category = $this->categoryRE->findOrFail($id);
        $category = $this->categoryRE->writeFromRequest($request, $category);

        return $category;
    }

    public function create(Request $request): Category
    {
        $category = $this->categoryRE->writeFromRequest($request);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $category;
    }

    public function delete(int $id): void
    {
        $category = $this->categoryRE->findOrFail($id);
        $this->categoryRE->remove($category);
    }

    // private function checkIfHavePagination($request): void
    // {
    //     if (null === $request->get('nPage') || null === $request->get('nReturns')) {
    //         throw new NotFoundException('Datos inválidos');
    //     }
    // }
}
