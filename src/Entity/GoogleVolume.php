<?php

namespace App\Entity;

use App\Repository\GoogleVolumeRepository;
use DateMalformedStringException;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoogleVolumeRepository::class)]
class GoogleVolume
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $volumeId = null;

    #[ORM\Column]
    private array $data = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?array $authors = null;

    #[ORM\Column(nullable: true)]
    private ?array $categories = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $publishedDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $publisher = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $userId = null;

    #[ORM\Column(nullable: true)]
    private ?int $pageCount = null;

    public function __construct(string $userId, array $data = [])
    {
        $this->userId = $userId;

        $this->data = $data;
        $this->volumeId = $data['id'] ?? null;

        if (isset($data['volumeInfo'])) {
            $volumeInfo = $data['volumeInfo'];

            $this->title = $volumeInfo['title'] ?? null;
            $this->authors = $volumeInfo['authors'] ?? null;
            $this->categories = $volumeInfo['categories'] ?? null;
            $this->description = $volumeInfo['description'] ?? null;
            $this->publisher = $volumeInfo['publisher'] ?? null;
            $this->subtitle = $volumeInfo['subtitle'] ?? null;
            $this->pageCount = $volumeInfo['pageCount'] ?? null;

            if (isset($volumeInfo['imageLinks'])) {
                $links = $volumeInfo['imageLinks'];

                $this->thumbnail = $links['thumbnail'] ?? null;
                $this->image = $links['extraLarge'] ??
                    $links['large'] ??
                    $links['medium'] ??
                    $links['small'] ??
                    $links['thumbnail'] ??
                    null;
            }

            try {
                $this->publishedDate = isset($volumeInfo['publishedDate']) ? new DateTime($volumeInfo['publishedDate']) : null;
            } catch (DateMalformedStringException $e) {
                $this->publishedDate = null;
            }
        }
    }

    public function updateFromArray(array $data = []): static
    {
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['authors'])) {
            $this->authors = $data['authors'];
        }
        if (isset($data['categories'])) {
            $this->categories = $data['categories'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['publisher'])) {
            $this->publisher = $data['publisher'];
        }
        if (isset($data['subtitle'])) {
            $this->subtitle = $data['subtitle'];
        }
        if (isset($data['pageCount'])) {
            $this->pageCount = $data['pageCount'];
        }
        if (isset($data['image'])) {
            $this->image = $data['image'];
        }
        if (isset($data['thumbnail'])) {
            $this->thumbnail = $data['thumbnail'];
        }

        if (isset($data['publishedDate'])) {
            try {
                $this->publishedDate = isset($volumeInfo['publishedDate']) ? new DateTime($volumeInfo['publishedDate']) : null;
            } catch (DateMalformedStringException $e) {
                $this->publishedDate = null;
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVolumeId(): ?string
    {
        return $this->volumeId;
    }

    public function setVolumeId(string $volumeId): static
    {
        $this->volumeId = $volumeId;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthors(): ?array
    {
        return $this->authors;
    }

    public function setAuthors(?array $authors): static
    {
        $this->authors = $authors;

        return $this;
    }

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function setCategories(?array $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPublishedDate(): ?DateTimeInterface
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(?DateTimeInterface $publishedDate): static
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(?string $publisher): static
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
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

    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    public function setPageCount(?int $pageCount): static
    {
        $this->pageCount = $pageCount;

        return $this;
    }
}
