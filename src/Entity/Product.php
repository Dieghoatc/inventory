<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Product
{
    const STATUS_COMFIRMED = 1;

    const STATUS_PENDING_TO_CONFIRM = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @Assert\NotEqualTo("·")
     * @Assert\NotEqualTo("CODE")
     * @Assert\NotNull()
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @Assert\NotEqualTo("PRODUCT")
     * @Assert\NotNull()
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $detail;

    /**
     * @ORM\Column(type="integer", options={"default" : 0}, nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductWarehouse", mappedBy="ddd", orphanRemoval=true)
     */
    private $warehouse;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductWarehouse", mappedBy="product")
     */
    private $productWarehouses;

    public function __construct()
    {
        $this->warehouse = new ArrayCollection();
        $this->productWarehouses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @ORM\PrePersist
     */
    public function setUuid(): self
    {
        $uuid1 = Uuid::uuid1();
        $this->uuid = $uuid1->toString();
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function addQuantity(int $quantity): void
    {
        $this->quantity += $quantity;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|ProductWarehouse[]
     */
    public function getProductWarehouses(): Collection
    {
        return $this->productWarehouses;
    }

    public function addProductWarehouse(ProductWarehouse $productWarehouse): self
    {
        if (!$this->productWarehouses->contains($productWarehouse)) {
            $this->productWarehouses[] = $productWarehouse;
            $productWarehouse->setProduct($this);
        }

        return $this;
    }

    public function removeProductWarehouse(ProductWarehouse $productWarehouse): self
    {
        if ($this->productWarehouses->contains($productWarehouse)) {
            $this->productWarehouses->removeElement($productWarehouse);
            // set the owning side to null (unless already changed)
            if ($productWarehouse->getProduct() === $this) {
                $productWarehouse->setProduct(null);
            }
        }

        return $this;
    }
}
