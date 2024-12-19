<?php

namespace App\Entity;

use App\Repository\MagazineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Status;

#[ORM\Entity(repositoryClass: MagazineRepository::class)]
class Magazine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $issn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $editionYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $editionMonth = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $number = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?bool $isSpecialEdition = null;

    #[ORM\ManyToOne(inversedBy: 'magazines')]
    private ?Publisher $publisher = null;

    #[ORM\ManyToOne(inversedBy: 'magazines')]
    private ?EditorialLine $editorialLine = null;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    private Status $status;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'magazines')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIssn(): ?string
    {
        return $this->issn;
    }

    public function setIssn(?string $issn): static
    {
        $this->issn = $issn;

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

    public function getEditionYear(): ?string
    {
        return $this->editionYear;
    }

    public function setEditionYear(?string $editionYear): static
    {
        $this->editionYear = $editionYear;

        return $this;
    }

    public function getEditionMonth(): ?string
    {
        return $this->editionMonth;
    }

    public function setEditionMonth(?string $editionMonth): static
    {
        $this->editionMonth = $editionMonth;

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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function isSpecialEdition(): ?bool
    {
        return $this->isSpecialEdition;
    }

    public function setSpecialEdition(bool $isSpecialEdition): static
    {
        $this->isSpecialEdition = $isSpecialEdition;

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

    public function getEditorialLine(): ?EditorialLine
    {
        return $this->editorialLine;
    }

    public function setEditorialLine(?EditorialLine $editorialLine): static
    {
        $this->editorialLine = $editorialLine;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function removeAllCategories(): static
    {
        $this->categories->clear();

        return $this;
    }

    // Getters and setters for $status
    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;
        return $this;
    }
}
