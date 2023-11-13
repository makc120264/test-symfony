<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 3,
        max: 12,
        minMessage: 'Title must be at least {{ limit }} characters long',
        maxMessage: 'Title cannot be longer than {{ limit }} characters',
    )]
    private ?string $title = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $eid = null;

    /**
     * @var ArrayCollection|Collection
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'categories')]
    private Collection|ArrayCollection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addCategory($this);
        }

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeCategory($this);
        }

        return $this;
    }
}
