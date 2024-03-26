<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\Table(name: 'movies')]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Orm\Column(name: 'title', type: 'string', nullable: false)]
    private ?string $title = null;

    #[Orm\Column(name: 'release_year', type: 'string', nullable: false)]
    private ?string $releaseYear = null;

    #[Orm\Column(name: 'format', type: 'string', nullable: false, columnDefinition: "ENUM('DVD', 'VHS', 'Blu-ray')")]
    private ?string $format = null;

    #[Orm\OneToMany(targetEntity: MovieStars::class, mappedBy: 'movie')]
    private ?Collection $movieStars;

    public function __construct()
    {
        $this->movieStars = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getReleaseYear(): ?string
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(?string $releaseYear): void
    {
        $this->releaseYear = $releaseYear;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    public function getMovieStars(): ?Collection
    {
        return $this->movieStars;
    }

    public function addMovieStars(MovieStars $movieStars): self
    {
        if (!$this->movieStars->contains($movieStars)) {
            $this->movieStars->add($movieStars);
            $movieStars->setMovie($this);
        }
        return $this;
    }

    public function removeMovieStars(MovieStars $movieStars): self
    {
        if($this->movieStars->removeElement($movieStars)) {
            if($movieStars->getMovie() === $this) {
                $movieStars->setMovie(null);
            }
        }

        return $this;
    }
}