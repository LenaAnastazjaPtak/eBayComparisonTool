<?php

namespace App\Entity;

use App\Repository\ProductVariantFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductVariantFileRepository::class)]
class ProductVariantFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productVariantFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductVariant $productVariant = null;

    #[ORM\Column]
    private ?bool $toDelete = null;

    #[ORM\ManyToOne(inversedBy: 'productVariantFiles')]
    private ?File $file = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductVariant(): ?ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariant $productVariant): static
    {
        $this->productVariant = $productVariant;

        return $this;
    }

    public function isToDelete(): ?bool
    {
        return $this->toDelete;
    }

    public function setToDelete(bool $toDelete): static
    {
        $this->toDelete = $toDelete;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): static
    {
        $this->file = $file;

        return $this;
    }
}
