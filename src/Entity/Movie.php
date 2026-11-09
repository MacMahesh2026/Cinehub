<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute\Uploadable;
use Vich\UploaderBundle\Mapping\Attribute\UploadableField;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[Uploadable]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $releaseDate = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $posterUrl = null;

    /**
     * Name of the poster object stored in the configured storage (S3 in prod).
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterName = null;

    #[ORM\Column(nullable: true)]
    private ?int $posterSize = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterMimeType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterOriginalName = null;

    /**
     * Bumped whenever a new poster is selected; Vich uses it to detect changes.
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $posterUpdatedAt = null;

    #[UploadableField(
        mapping: 'movie_poster',
        fileNameProperty: 'posterName',
        size: 'posterSize',
        mimeType: 'posterMimeType',
        originalName: 'posterOriginalName',
    )]
    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG or WebP).',
    )]
    private ?File $posterFile = null;

    /**
     * @var Collection<int, Showtime>
     */
    #[ORM\OneToMany(targetEntity: Showtime::class, mappedBy: 'movie')]
    private Collection $showtimes;

    public function __construct()
    {
        $this->showtimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getPosterUrl(): ?string
    {
        return $this->posterUrl;
    }

    public function setPosterUrl(?string $posterUrl): static
    {
        $this->posterUrl = $posterUrl;

        return $this;
    }

    public function getPosterName(): ?string
    {
        return $this->posterName;
    }

    public function setPosterName(?string $posterName): static
    {
        $this->posterName = $posterName;

        return $this;
    }

    public function getPosterSize(): ?int
    {
        return $this->posterSize;
    }

    public function setPosterSize(?int $posterSize): static
    {
        $this->posterSize = $posterSize;

        return $this;
    }

    public function getPosterMimeType(): ?string
    {
        return $this->posterMimeType;
    }

    public function setPosterMimeType(?string $posterMimeType): static
    {
        $this->posterMimeType = $posterMimeType;

        return $this;
    }

    public function getPosterOriginalName(): ?string
    {
        return $this->posterOriginalName;
    }

    public function setPosterOriginalName(?string $posterOriginalName): static
    {
        $this->posterOriginalName = $posterOriginalName;

        return $this;
    }

    public function getPosterUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->posterUpdatedAt;
    }

    public function setPosterUpdatedAt(?\DateTimeImmutable $posterUpdatedAt): static
    {
        $this->posterUpdatedAt = $posterUpdatedAt;

        return $this;
    }

    public function getPosterFile(): ?File
    {
        return $this->posterFile;
    }

    public function setPosterFile(?File $posterFile): static
    {
        $this->posterFile = $posterFile;

        // Vich requires a mapped field to change so Doctrine detects the update.
        if (null !== $posterFile) {
            $this->posterUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return Collection<int, Showtime>
     */
    public function getShowtimes(): Collection
    {
        return $this->showtimes;
    }

    public function addShowtime(Showtime $showtime): static
    {
        if (!$this->showtimes->contains($showtime)) {
            $this->showtimes->add($showtime);
            $showtime->setMovie($this);
        }

        return $this;
    }

    public function removeShowtime(Showtime $showtime): static
    {
        if ($this->showtimes->removeElement($showtime)) {
            // set the owning side to null (unless already changed)
            if ($showtime->getMovie() === $this) {
                $showtime->setMovie(null);
            }
        }

        return $this;
    }
}
