<?php

namespace App\Entity;

use App\Repository\SeatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeatRepository::class)]
class Seat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $seatNumber = null;

    #[ORM\Column(length: 10)]
    private ?string $rowName = null;

    #[ORM\Column(length: 20)]
    private ?string $seatType = null;

    #[ORM\ManyToOne(inversedBy: 'seats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Screen $screen = null;

    /**
     * @var Collection<int, BookingSeat>
     */
    #[ORM\OneToMany(targetEntity: BookingSeat::class, mappedBy: 'seat')]
    private Collection $bookingSeats;

    public function __construct()
    {
        $this->bookingSeats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeatNumber(): ?string
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(string $seatNumber): static
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    public function getRowName(): ?string
    {
        return $this->rowName;
    }

    public function setRowName(string $rowName): static
    {
        $this->rowName = $rowName;

        return $this;
    }

    public function getSeatType(): ?string
    {
        return $this->seatType;
    }

    public function setSeatType(string $seatType): static
    {
        $this->seatType = $seatType;

        return $this;
    }

    public function getScreen(): ?Screen
    {
        return $this->screen;
    }

    public function setScreen(?Screen $screen): static
    {
        $this->screen = $screen;

        return $this;
    }

    /**
     * @return Collection<int, BookingSeat>
     */
    public function getBookingSeats(): Collection
    {
        return $this->bookingSeats;
    }

    public function addBookingSeat(BookingSeat $bookingSeat): static
    {
        if (!$this->bookingSeats->contains($bookingSeat)) {
            $this->bookingSeats->add($bookingSeat);
            $bookingSeat->setSeat($this);
        }

        return $this;
    }

    public function removeBookingSeat(BookingSeat $bookingSeat): static
    {
        if ($this->bookingSeats->removeElement($bookingSeat)) {
            // set the owning side to null (unless already changed)
            if ($bookingSeat->getSeat() === $this) {
                $bookingSeat->setSeat(null);
            }
        }

        return $this;
    }
}
