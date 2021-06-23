<?php

namespace App\Entity;

use App\Repository\KentekenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=KentekenRepository::class)
 */
class Kenteken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $kenteken;

    /**
     * @ORM\Column(type="boolean")
     */
    private $connection;

    /**
     * @ORM\Column(type="string", length=510, nullable=true)
     */
    private $output;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKenteken(): ?string
    {
        return $this->kenteken;
    }

    public function setKenteken(string $kenteken): self
    {
        $this->kenteken = $kenteken;

        return $this;
    }

    public function getConnection(): ?bool
    {
        return $this->connection;
    }

    public function setConnection(bool $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): self
    {
        $this->output = $output;

        return $this;
    }
}
