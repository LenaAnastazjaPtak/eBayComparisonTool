<?php

namespace App\Entity;

use App\Repository\ProductVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
class ProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $variantCode = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'productVariants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(length: 255)]
    private ?string $material = null;

    /**
     * @var Collection<int, ProductVariantFile>
     */
    #[ORM\OneToMany(targetEntity: ProductVariantFile::class, mappedBy: 'productVariant')]
    private Collection $productVariantFiles;

    public function __construct()
    {
        $this->productVariantFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVariantCode(): ?string
    {
        return $this->variantCode;
    }

    public function setVariantCode(string $variantCode): static
    {
        $this->variantCode = $variantCode;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(string $material): static
    {
        $this->material = $material;

        return $this;
    }

    /**
     * @return Collection<int, ProductVariantFile>
     */
    public function getProductVariantFiles(): Collection
    {
        return $this->productVariantFiles;
    }

    public function addProductVariantFile(ProductVariantFile $productVariantFile): static
    {
        if (!$this->productVariantFiles->contains($productVariantFile)) {
            $this->productVariantFiles->add($productVariantFile);
            $productVariantFile->setProductVariant($this);
        }

        return $this;
    }

    public function removeProductVariantFile(ProductVariantFile $productVariantFile): static
    {
        if ($this->productVariantFiles->removeElement($productVariantFile)) {
            // set the owning side to null (unless already changed)
            if ($productVariantFile->getProductVariant() === $this) {
                $productVariantFile->setProductVariant(null);
            }
        }

        return $this;
    }
}
