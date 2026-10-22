<?php

namespace App\Entity;

use App\Repository\ScreenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreenRepository::class)]
class Screen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $totalSeats = null;

    #[ORM\ManyToOne(inversedBy: 'screens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theatre $theatre = null;

    /**
     * @var Collection<int, Seat>
     */
    #[ORM\OneToMany(targetEntity: Seat::class, mappedBy: 'screen')]
    private Collection $seats;

    /**
     * @var Collection<int, Showtime>
     */
    #[ORM\OneToMany(targetEntity: Showtime::class, mappedBy: 'screen')]
    private Collection $showtimes;

    public function __construct()
    {
        $this->seats = new ArrayCollection();
        $this->showtimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTotalSeats(): ?int
    {
        return $this->totalSeats;
    }

    public function setTotalSeats(int $totalSeats): static
    {
        $this->totalSeats = $totalSeats;

        return $this;
    }

    public function getTheatre(): ?Theatre
    {
        return $this->theatre;
    }

    public function setTheatre(?Theatre $theatre): static
    {
        $this->theatre = $theatre;

        return $this;
    }

    /**
     * @return Collection<int, Seat>
     */
    public function getSeats(): Collection
    {
        return $this->seats;
    }

    public function addSeat(Seat $seat): static
    {
        if (!$this->seats->contains($seat)) {
            $this->seats->add($seat);
            $seat->setScreen($this);
        }

        return $this;
    }

    public function removeSeat(Seat $seat): static
    {
        if ($this->seats->removeElement($seat)) {
            // set the owning side to null (unless already changed)
            if ($seat->getScreen() === $this) {
                $seat->setScreen(null);
            }
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
            $showtime->setScreen($this);
        }

        return $this;
    }

    public function removeShowtime(Showtime $showtime): static
    {
        if ($this->showtimes->removeElement($showtime)) {
            // set the owning side to null (unless already changed)
            if ($showtime->getScreen() === $this) {
                $showtime->setScreen(null);
            }
        }

        return $this;
    }
}
