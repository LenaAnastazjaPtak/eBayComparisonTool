<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $originalName = null;

    #[ORM\Column(length: 255)]
    private ?string $format = null;

    /**
     * @var Collection<int, ProductVariantFile>
     */
    #[ORM\OneToMany(targetEntity: ProductVariantFile::class, mappedBy: 'file')]
    private Collection $productVariantFiles;

    public function __construct(
        $file = null,
        $filename = null,
        $ftp = null,
        string $dir = 'images/products/',
        bool $newFile = true)
    {
        if ($file != null) {
            $this->sendFile($file, $filename, $ftp, $dir, $newFile);
        }
        $this->productVariantFiles = new ArrayCollection();
    }

    public function getFilepath(): string
    {
        return __DIR__ . "/../../public/uploads/images/products/" . $this->filename;
    }

    public function sendFile($file, $filename, $ftp, string $dir, bool $newFile): void
    {
        $this->deleteFile();
        $path = __DIR__ . "/../../public/uploads/" . $dir;
        $this->setOriginalName(basename($file));
        $ext = substr(strrchr($this->getOriginalname(), '.'), 1);

        if ($newFile) {
            if ($file instanceof UploadedFile) {
                $ext = strtolower($file->getClientOriginalExtension());
                $this->setOriginalName($file->getClientOriginalName());
                if (!$filename) {
                    $filename = "00xd00" . uniqid() . "." . $ext;
                }
                if ($ftp)
                    $ftp->put($filename, $file, FTP_BINARY);
                else
                    $file->move($path, $filename);
            } else {
                if (!$filename) {
                    $filename = uniqid() . "." . $ext;
                }
                if ($ftp) {
                    try {
                        $ftp->fput($filename, fopen($file, 'r'), FTP_BINARY);
                    } catch (Exception $ex) {
                    }
                } else {
                    try {
                        file_put_contents($path . $filename, fopen($file, 'r'));
                    } catch (Exception $ex) {
                    }
                }
            }
        }

        $this->setFilename($filename);
        $this->setFormat($ext);
    }


    public function deleteFile($ftp = null): void
    {
        if ($ftp) {
            @ $ftp->delete($this->getFilename());
            @ $ftp->delete($this->getFilename('png'));
        } else {
            $path = __DIR__ . "/../../public/uploads/images/products/";
            if ($this->getFilename() && file_exists($path . $this->getFilename())) {
                @ unlink($path . $this->getFilename());
            }
            if ($this->getFilename() && file_exists($path . $this->getFilename('png'))) {
                @ unlink($path . $this->getFilename('png'));
            }
        }
    }

    static function checkIfUrlExists($filename): bool
    {
        $file_headers = @get_headers($filename);
        try {
            if ($file_headers[0] == 'HTTP/1.0 404 Not Found') {
                return false;
            } else if ($file_headers[0] == 'HTTP/1.0 302 Found' && $file_headers[7] == 'HTTP/1.0 404 Not Found') {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): static
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;

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
            $productVariantFile->setFile($this);
        }

        return $this;
    }

    public function removeProductVariantFile(ProductVariantFile $productVariantFile): static
    {
        if ($this->productVariantFiles->removeElement($productVariantFile)) {
            // set the owning side to null (unless already changed)
            if ($productVariantFile->getFile() === $this) {
                $productVariantFile->setFile(null);
            }
        }

        return $this;
    }
}
