<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'products')]
    private Collection $categories;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Length(
        min: 3,
        max: 12,
        minMessage: 'Title must be at least {{ limit }} characters long',
        maxMessage: 'Title cannot be longer than {{ limit }} characters',
    )]
    private ?string $title = null;

    /**
     * @var float|null
     */
    #[ORM\Column]
    #[Range(
        notInRangeMessage: 'You must be between {{ min }}cm and {{ max }}cm tall to enter',
        min: 0,
        max: 200
    )]
    private ?float $price = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $eid = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEid(): ?int
    {
        return $this->eid;
    }

    /**
     * @param int|null $eid
     * @return $this
     */
    public function setEid(?int $eid): static
    {
        $this->eid = $eid;

        return $this;
    }
}
