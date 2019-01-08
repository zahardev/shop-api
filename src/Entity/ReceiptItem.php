<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReceiptItemRepository")
 */
class ReceiptItem
{
    const MONEY_PRECISION = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Receipt", inversedBy="receiptItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $receipt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="float")
     */
    private $cost;

    /**
     * @ORM\Column(type="integer")
     */
    private $vatClass;

    /**
     * @ORM\Column(type="float")
     */
    private $vat;

    /**
     * @ORM\Column(type="float")
     */
    private $costWithVat;

    /**
     * @ORM\Column(type="float")
     */
    private $total;

    /**
     * @ORM\Column(type="float")
     */
    private $totalVat;

    /**
     * @ORM\Column(type="float")
     */
    private $totalWithVat;


    public function __construct(Product $product, int $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
        $this->recalculate();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceipt(): ?Receipt
    {
        return $this->receipt;
    }

    public function setReceipt(?Receipt $receipt): self
    {
        $this->receipt = $receipt;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }


    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function addQuantity(int $quantity): self
    {
        $this->quantity += $quantity;
        $this->recalculate();

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    private function setCost(float $cost): self
    {
        $this->cost = round($cost, self::MONEY_PRECISION);

        return $this;
    }

    public function getVat(): ?float
    {
        return $this->vat;
    }

    private function setVat(float $vat): self
    {
        $this->vat = round($vat, self::MONEY_PRECISION);

        return $this;
    }

    public function getCostWithVat(): ?float
    {
        return $this->costWithVat;
    }

    private function setCostWithVat(float $costWithVat): self
    {
        $this->costWithVat = round($costWithVat, self::MONEY_PRECISION);

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    private function setTotal(float $total): self
    {
        $this->total = round($total, self::MONEY_PRECISION);

        return $this;
    }

    public function getTotalVat(): ?float
    {
        return $this->totalVat;
    }

    private function setTotalVat(float $totalVat): self
    {
        $this->totalVat = round($totalVat, self::MONEY_PRECISION);

        return $this;
    }

    public function getTotalWithVat(): ?float
    {
        return $this->totalWithVat;
    }

    public function setTotalWithVat(float $totalWithVat): self
    {
        $this->totalWithVat = $totalWithVat;

        return $this;
    }

    public function getVatClass(): ?int
    {
        return $this->vatClass;
    }

    private function setVatClass(int $vatClass): self
    {
        $this->vatClass = $vatClass;

        return $this;
    }

    private function recalculate()
    {
        //copy product properties here for history (in case if product changes)
        $this->setCost($this->product->getCost());
        $this->setVatClass($this->product->getVatClass()->getPercent());

        $this->setVat($this->cost * $this->vatClass / 100);
        $this->setCostWithVat($this->cost + $this->vat);
        $this->setTotal($this->cost * $this->quantity);
        $this->setTotalVat($this->total * $this->vatClass / 100);
        $this->setTotalWithVat($this->total + $this->totalVat);
    }


    public function toArray(): array
    {
        return [
            'name' => $this->getProduct()->getName(),
            'barcode' => $this->getProduct()->getBarcode(),
            'cost' => $this->getCost(),
            'quantity' => $this->getQuantity(),
            'vatClass' => $this->getVatClass(),
            'vat' => $this->getVat(),
            'costWithVat' => $this->getCostWithVat(),
            'total' => $this->getTotal(),
            'totalVat' => $this->getTotalVat(),
            'totalWithVat' => $this->getTotalWithVat(),
        ];
    }

}
