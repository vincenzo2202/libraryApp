<?php

namespace App\Entity;

use App\Repository\EditorialLineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EditorialLineRepository::class)]
class EditorialLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'editorialLines')]
    private ?Publisher $publisher = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'editorialLine')]
    private Collection $books;

    /**
     * @var Collection<int, Magazine>
     */
    #[ORM\OneToMany(targetEntity: Magazine::class, mappedBy: 'editorialLine')]
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;

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

    public function getPublisher(): ?Publisher
    {
        return $this->publisher;
    }

    public function setPublisher(?Publisher $publisher): static
    {
        $this->publisher = $publisher;

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
            $book->setEditorialLine($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getEditorialLine() === $this) {
                $book->setEditorialLine(null);
            }
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
            $magazine->setEditorialLine($this);
        }

        return $this;
    }

    public function removeMagazine(Magazine $magazine): static
    {
        if ($this->magazines->removeElement($magazine)) {
            // set the owning side to null (unless already changed)
            if ($magazine->getEditorialLine() === $this) {
                $magazine->setEditorialLine(null);
            }
        }

        return $this;
    }
}
