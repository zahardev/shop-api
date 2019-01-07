<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
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
     * @ORM\Column(type="bigint")
     */
    private $barcode;


    /**
     * @ORM\Column(type="float")
     */
    private $cost;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\VatClass", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vatClass;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBarcode(): ?int
    {
        return $this->barcode;
    }

    public function setBarcode(int $barcode): self
    {
        $this->barcode = $barcode;

        return $this;
    }
    

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
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

    public function getVatClass(): ?VatClass
    {
        return $this->vatClass;
    }

    public function setVatClass(?VatClass $vatClass): self
    {
        $this->vatClass = $vatClass;

        return $this;
    }
}
