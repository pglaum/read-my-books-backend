<?php

namespace App\Entity;

use App\Repository\BookEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookEventRepository::class)]
class BookEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: BookEventType::class)]
    private ?BookEventType $event = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?SavedBook $savedBook = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?BookEventType
    {
        return $this->event;
    }

    public function setEvent(BookEventType $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getSavedBook(): ?SavedBook
    {
        return $this->savedBook;
    }

    public function setSavedBook(?SavedBook $savedBook): static
    {
        $this->savedBook = $savedBook;

        return $this;
    }
}
