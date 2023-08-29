<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Review extends BaseEntity
{
    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min=1, max=10)
     */
    private $starRating;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $reviewText;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Car")
     * @ORM\JoinColumn(nullable=false)
     */
    private $car;





    public function getStarRating(): ?int
    {
        return $this->starRating;
    }

    public function setStarRating(int $starRating): self
    {
        $this->starRating = $starRating;

        return $this;
    }

    public function getReviewText(): ?string
    {
        return $this->reviewText;
    }

    public function setReviewText(string $reviewText): self
    {
        $this->reviewText = $reviewText;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): self
    {
        $this->car = $car;

        return $this;
    }
}