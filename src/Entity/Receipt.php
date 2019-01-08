<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReceiptRepository")
 */
class Receipt
{
    const STATUS_UNFINISHED = 'unfinished';

    const STATUS_FINISHED = 'finished';


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=36, unique=true))
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReceiptItem", mappedBy="receipt", orphanRemoval=true, cascade={"persist"})
     */
    private $receiptItems;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalVat;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalWithVat;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total21;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalVat21;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalWithVat21;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total6;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalVat6;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalWithVat6;

    /**
     * Receipt constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->receiptItems = new ArrayCollection();
        $this->status = self::STATUS_UNFINISHED;
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Should be used only for fixtures
     * @param string $uuid
     * @return Receipt
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, array(self::STATUS_UNFINISHED, self::STATUS_FINISHED))) {
            throw new \InvalidArgumentException("Invalid status");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|ReceiptItem[]
     */
    public function getReceiptItems(): Collection
    {
        return $this->receiptItems;
    }

    public function addReceiptItem(ReceiptItem $receiptItem): self
    {
        if (!$this->receiptItems->contains($receiptItem)) {
            $this->receiptItems[] = $receiptItem;
            $receiptItem->setReceipt($this);
        }

        $this->recalculate();

        return $this;
    }

    public function removeReceiptItem(ReceiptItem $receiptItem): self
    {
        if ($this->receiptItems->contains($receiptItem)) {
            $this->receiptItems->removeElement($receiptItem);
            // set the owning side to null (unless already changed)
            if ($receiptItem->getReceipt() === $this) {
                $receiptItem->setReceipt(null);
            }
        }

        $this->recalculate();

        return $this;
    }


    public function getTotal(): ?float
    {
        return $this->total;
    }

    private function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getTotalVat(): ?float
    {
        return $this->totalVat;
    }

    private function setTotalVat(?float $totalVat): self
    {
        $this->totalVat = $totalVat;

        return $this;
    }

    public function getTotalWithVat(): ?float
    {
        return $this->totalWithVat;
    }

    private function setTotalWithVat(float $totalWithVat): self
    {
        $this->totalWithVat = $totalWithVat;

        return $this;
    }

    public function getTotal21(): ?float
    {
        return $this->total21;
    }

    private function setTotal21(?float $total21): self
    {
        $this->total21 = $total21;

        return $this;
    }

    public function getTotalVat21(): ?float
    {
        return $this->totalVat21;
    }

    private function setTotalVat21(?float $totalVat21): self
    {
        $this->totalVat21 = $totalVat21;

        return $this;
    }

    public function getTotalWithVat21(): ?float
    {
        return $this->totalWithVat21;
    }

    private function setTotalWithVat21(?float $totalWithVat21): self
    {
        $this->totalWithVat21 = $totalWithVat21;

        return $this;
    }

    public function getTotal6(): ?float
    {
        return $this->total6;
    }

    private function setTotal6(?float $total6): self
    {
        $this->total6 = $total6;

        return $this;
    }

    public function getTotalVat6(): ?float
    {
        return $this->totalVat6;
    }

    private function setTotalVat6(?float $totalVat6): self
    {
        $this->totalVat6 = $totalVat6;

        return $this;
    }

    public function getTotalWithVat6(): ?float
    {
        return $this->totalWithVat6;
    }

    private function setTotalWithVat6(?float $totalWithVat6): self
    {
        $this->totalWithVat6 = $totalWithVat6;

        return $this;
    }


    protected function recalculate()
    {
        $totals = [];
        $totalVats = [];
        $totalWithVats = [];

        $totals21 = [];
        $totalVats21 = [];
        $totalWithVats21 = [];

        $totals6 = [];
        $totalVats6 = [];
        $totalWithVats6 = [];

        foreach ($this->getReceiptItems()->toArray() as $receiptItem) {
            /** @var ReceiptItem $receiptItem */
            $vatClass = $receiptItem->getVatClass();

            $totals[] = $receiptItem->getTotal();
            ${'totals'.$vatClass}[] = $receiptItem->getTotal();

            $totalVats[] = $receiptItem->getTotalVat();
            ${'totalVats'.$vatClass}[] = $receiptItem->getTotalVat();

            $totalWithVats[] = $receiptItem->getTotalWithVat();
            ${'totalWithVats'.$vatClass}[] = $receiptItem->getTotalWithVat();
        }

        $this->setTotal(array_sum($totals));
        $this->setTotalVat(array_sum($totalVats));
        $this->setTotalWithVat(array_sum($totalWithVats));

        $this->setTotal21(array_sum($totals21));
        $this->setTotalVat21(array_sum($totalVats21));
        $this->setTotalWithVat21(array_sum($totalWithVats21));

        $this->setTotal6(array_sum($totals6));
        $this->setTotalVat6(array_sum($totalVats6));
        $this->setTotalWithVat6(array_sum($totalWithVats6));
    }

    public function toArray(): array
    {
        $items = $this->getReceiptItems()->toArray();
        foreach ($items as $k => $item) {
            $items[$k] = $item->toArray();
        }

        return [
            'status' => $this->getStatus(),
            'uuid' => $this->getUuid(),
            'items' => $items,
            'total' => $this->getTotal(),
            'totalVat' => $this->getTotalVat(),
            'totalWithVat' => $this->getTotalWithVat(),
            'total21' => $this->getTotal21(),
            'totalVat21' => $this->getTotalVat21(),
            'totalWithVat21' => $this->getTotalWithVat21(),
            'total6' => $this->getTotal6(),
            'totalVat6' => $this->getTotalVat6(),
            'totalWithVat6' => $this->getTotalWithVat6(),
        ];
    }
}
