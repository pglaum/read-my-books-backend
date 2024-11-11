<?php

namespace App\Entity;

use App\Repository\SavedBookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavedBookRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class SavedBook
{
    use TimestampsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $userId = null;

    #[ORM\ManyToOne]
    private ?GoogleVolume $volume = null;

    #[ORM\Column(nullable: true)]
    private ?int $ownedCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $pageProgress = null;

    #[ORM\Column(nullable: true, enumType: BookList::class)]
    private ?BookList $bookList = null;

    /** @var Collection<int, BookEvent> */
    #[ORM\OneToMany(targetEntity: BookEvent::class, mappedBy: 'savedBook')]
    private Collection $events;

    #[ORM\Column(nullable: true, enumType: BookStatusType::class)]
    private ?BookStatusType $bookStatus = null;

    #[ORM\Column(nullable: true)]
    private ?bool $favorite = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getVolume(): ?GoogleVolume
    {
        return $this->volume;
    }

    public function setVolume(?GoogleVolume $volume): static
    {
        $this->volume = $volume;

        return $this;
    }

    public function getOwnedCount(): ?int
    {
        return $this->ownedCount;
    }

    public function setOwnedCount(?int $ownedCount): static
    {
        $this->ownedCount = $ownedCount;

        return $this;
    }

    public function getPageProgress(): ?int
    {
        return $this->pageProgress;
    }

    public function setPageProgress(?int $pageProgress): static
    {
        $this->pageProgress = $pageProgress;

        return $this;
    }

    public function getBookList(): ?BookList
    {
        return $this->bookList;
    }

    public function setBookList(?BookList $bookList): static
    {
        $this->bookList = $bookList;

        return $this;
    }

    /**
     * @return Collection<int, BookEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(BookEvent $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setSavedBook($this);
        }

        return $this;
    }

    public function removeEvent(BookEvent $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getSavedBook() === $this) {
                $event->setSavedBook(null);
            }
        }

        return $this;
    }

    public function isOwned(): bool
    {
        return $this->hasEvent(BookEventType::BOUGHT);
    }

    public function hasEvent(BookEventType $eventType): bool
    {
        foreach ($this->events as $event) {
            if ($eventType === $event->getEvent()) {
                return true;
            }
        }

        return false;
    }

    public function isRead(): bool
    {
        return $this->hasEvent(BookEventType::READ);
    }

    public function getBookStatus(): ?BookStatusType
    {
        return $this->bookStatus;
    }

    public function setBookStatus(?BookStatusType $bookStatus): static
    {
        $this->bookStatus = $bookStatus;

        return $this;
    }

    public function isFavorite(): ?bool
    {
        return $this->favorite;
    }

    public function setFavorite(?bool $favorite): static
    {
        $this->favorite = $favorite;

        return $this;
    }
}
