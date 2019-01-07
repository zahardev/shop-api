<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReceiptItem", mappedBy="receipt", orphanRemoval=true)
     */
    private $receiptItems;

    public function __construct()
    {
        $this->receiptItems = new ArrayCollection();
        $this->status = self::STATUS_UNFINISHED;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
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

        return $this;
    }
}
