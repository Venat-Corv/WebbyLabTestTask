<?php

namespace App\Entity;

use App\Repository\MovieStarsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieStarsRepository::class)]
#[ORM\Table(name: 'movie_stars')]
class MovieStars
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Orm\ManyToOne(targetEntity: Movie::class, inversedBy: 'movieStars')]
    #[Orm\JoinColumn(nullable: false)]
    private ?Movie $movie = null;

    #[Orm\Column(name: 'name', type: 'string', nullable: false)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): void
    {
        $this->movie = $movie;
    }
}