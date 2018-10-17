<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"code", "warehouse"})
 */
class Product
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
     * @MaxDepth(2)
     * @ORM\ManyToOne(targetEntity="App\Entity\Warehouse", inversedBy="Products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $warehouse;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="integer", options={"default" : 0}, nullable=true)
     */
    private $status;

    const STATUS_COMFIRMED = 1;

    const STATUS_PENDING_TO_CONFIRM = 2;

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

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): self
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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
}
