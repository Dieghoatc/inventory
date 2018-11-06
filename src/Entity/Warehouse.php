<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WarehouseRepository")
 */
class Warehouse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductWarehouse", mappedBy="warehouse")
     */
    private $productWarehouses;

    public function __construct()
    {
        $this->Product = new ArrayCollection();
        $this->productWarehouses = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
            $productWarehouse->setWarehouse($this);
        }

        return $this;
    }

    public function removeProductWarehouse(ProductWarehouse $productWarehouse): self
    {
        if ($this->productWarehouses->contains($productWarehouse)) {
            $this->productWarehouses->removeElement($productWarehouse);
            // set the owning side to null (unless already changed)
            if ($productWarehouse->getWarehouse() === $this) {
                $productWarehouse->setWarehouse(null);
            }
        }

        return $this;
    }

}
