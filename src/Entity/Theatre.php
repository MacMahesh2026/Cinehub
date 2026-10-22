<?php

namespace App\Entity;

use App\Repository\TheatreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TheatreRepository::class)]
class Theatre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 500)]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    private ?string $city = null;

    /**
     * @var Collection<int, Screen>
     */
    #[ORM\OneToMany(targetEntity: Screen::class, mappedBy: 'theatre')]
    private Collection $screens;

    public function __construct()
    {
        $this->screens = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Screen>
     */
    public function getScreens(): Collection
    {
        return $this->screens;
    }

    public function addScreen(Screen $screen): static
    {
        if (!$this->screens->contains($screen)) {
            $this->screens->add($screen);
            $screen->setTheatre($this);
        }

        return $this;
    }

    public function removeScreen(Screen $screen): static
    {
        if ($this->screens->removeElement($screen)) {
            // set the owning side to null (unless already changed)
            if ($screen->getTheatre() === $this) {
                $screen->setTheatre(null);
            }
        }

        return $this;
    }
}
