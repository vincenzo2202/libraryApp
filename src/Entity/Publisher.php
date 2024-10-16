<?php

namespace App\Entity;

use App\Repository\PublisherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublisherRepository::class)]
class Publisher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'publishers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, EditorialLine>
     */
    #[ORM\OneToMany(targetEntity: EditorialLine::class, mappedBy: 'publisher')]
    private Collection $editorialLines;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'publisher')]
    private Collection $books;

    /**
     * @var Collection<int, Magazine>
     */
    #[ORM\OneToMany(targetEntity: Magazine::class, mappedBy: 'publisher')]
    private Collection $magazines;

    public function __construct()
    {
        $this->editorialLines = new ArrayCollection();
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, EditorialLine>
     */
    public function getEditorialLines(): Collection
    {
        return $this->editorialLines;
    }

    public function addEditorialLine(EditorialLine $editorialLine): static
    {
        if (!$this->editorialLines->contains($editorialLine)) {
            $this->editorialLines->add($editorialLine);
            $editorialLine->setPublisher($this);
        }

        return $this;
    }

    public function removeEditorialLine(EditorialLine $editorialLine): static
    {
        if ($this->editorialLines->removeElement($editorialLine)) {
            // set the owning side to null (unless already changed)
            if ($editorialLine->getPublisher() === $this) {
                $editorialLine->setPublisher(null);
            }
        }

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
            $book->setPublisher($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getPublisher() === $this) {
                $book->setPublisher(null);
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
            $magazine->setPublisher($this);
        }

        return $this;
    }

    public function removeMagazine(Magazine $magazine): static
    {
        if ($this->magazines->removeElement($magazine)) {
            // set the owning side to null (unless already changed)
            if ($magazine->getPublisher() === $this) {
                $magazine->setPublisher(null);
            }
        }

        return $this;
    }
}
