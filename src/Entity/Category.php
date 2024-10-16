<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'categories')]
    private Collection $books;

    /**
     * @var Collection<int, Magazine>
     */
    #[ORM\ManyToMany(targetEntity: Magazine::class, mappedBy: 'Categories')]
    private Collection $magazines;

    public function __construct()
    {
        $this->books = new ArrayCollection();
        $this->magazines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->addCategory($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            $book->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Magazine>
     */
    public function getMagazines(): Collection
    {
        return $this->magazines;
    }

    public function addMagazine(Magazine $magazine): static
    {
        if (!$this->magazines->contains($magazine)) {
            $this->magazines->add($magazine);
            $magazine->addCategory($this);
        }

        return $this;
    }

    public function removeMagazine(Magazine $magazine): static
    {
        if ($this->magazines->removeElement($magazine)) {
            $magazine->removeCategory($this);
        }

        return $this;
    }
}
