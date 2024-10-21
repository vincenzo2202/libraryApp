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
    private $security;

    public function __construct(
        private CategoryRepository $categoryRE,
        private EntityManagerInterface $em,
        Security $security
    ) {
        $this->security = $security;
    }

    public function getCategoryById($id)
    {
        $category = $this->categoryRE->findOrFail($id);
        $me = $this->tokenUserId();
        if ($category->getUser()->getId() !== $me) {
            throw new NotFoundException('Categoría no encontrada');
        }

        $formatedCategory = [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'color' => $category->getColor()
        ];

        return $formatedCategory;
    }

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

        if ($category->getUser()->getId() !== $this->tokenUserId()) {
            throw new NotFoundException('Categoría no encontrada');
        }

        $category = $this->categoryRE->writeFromRequest($request, $category);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

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

    public function tokenUserId(): int
    {
        return $this->security->getUser()->getId();
    }
}
