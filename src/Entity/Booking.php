<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Showtime $showtime = null;

    #[ORM\Column(length: 32)]
    private ?string $bookingReference = null;

    #[ORM\Column]
    private ?\DateTime $bookingDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(length: 32)]
    private ?string $status = null;

    /**
     * @var Collection<int, BookingSeat>
     */
    #[ORM\OneToMany(targetEntity: BookingSeat::class, mappedBy: 'booking')]
    private Collection $bookingSeats;

    public function __construct()
    {
        $this->bookingSeats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getShowtime(): ?Showtime
    {
        return $this->showtime;
    }

    public function setShowtime(?Showtime $showtime): static
    {
        $this->showtime = $showtime;

        return $this;
    }

    public function getBookingReference(): ?string
    {
        return $this->bookingReference;
    }

    public function setBookingReference(string $bookingReference): static
    {
        $this->bookingReference = $bookingReference;

        return $this;
    }

    public function getBookingDate(): ?\DateTime
    {
        return $this->bookingDate;
    }

    public function setBookingDate(\DateTime $bookingDate): static
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
            $bookingSeat->setBooking($this);
        }

        return $this;
    }

    public function removeBookingSeat(BookingSeat $bookingSeat): static
    {
        if ($this->bookingSeats->removeElement($bookingSeat)) {
            // set the owning side to null (unless already changed)
            if ($bookingSeat->getBooking() === $this) {
                $bookingSeat->setBooking(null);
            }
        }

        return $this;
    }
}
