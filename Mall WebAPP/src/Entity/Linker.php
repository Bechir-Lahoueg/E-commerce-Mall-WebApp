<?php

namespace App\Entity;

use App\Enum\Grade;
use App\Repository\LinkerRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: LinkerRepository::class)]

class Linker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'linker', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column]
    private ?bool $isActive = false;

    #[ORM\Column]
    private ?float $totalConsumption = 0;

    #[ORM\Column]
    private ?float $orderCommission = 0;
    
    #[ORM\Column]
    private ?float $solde = 0;

    #[ORM\Column]
    private ?bool $isPaid = false;

    #[ORM\Column]
    private ?float $earning = 0;

    #[ORM\Column]
    private ?float $teamConsumption = 0;

    #[ORM\Column]
    private ?float $parentComission = 0;

    #[ORM\Column]
    private ?float $monthlyConsumption = 0;

    #[ORM\Column(length: 50)]
    private string|Grade $grade = Grade::LINKER;

    #[ORM\Column]
    private ?float $teamMonthlyConsumption = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTotalConsumption(): ?float
    {
        return $this->totalConsumption;
    }

    public function setTotalConsumption(float $totalConsumption): static
    {
        $this->totalConsumption = $totalConsumption;

        return $this;
    }

    public function getOrderCommission(): ?float
    {
        return $this->orderCommission;
    }

    public function setOrderCommission(float $orderCommission): static
    {
        $this->orderCommission = $orderCommission;

        return $this;
    }
    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function getSoldes(): ?float
    {
        return $this->orderCommission + $this->parentComission;
    }

    public function setSolde(float $solde): static
    {
         $this->solde= $solde;
         return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getEarning(): ?float
    {
        return $this->earning;
    }

    public function setEarning(float $earning): static
    {
        $this->earning = $earning;

        return $this;
    }

    public function getTeamConsumption(): ?float
    {
        return $this->teamConsumption;
    }

    public function setTeamConsumption(float $teamConsumption): static
    {
        $this->teamConsumption = $teamConsumption;

        return $this;
    }

    public function getParentComission(): ?float
    {
        return $this->parentComission;
    }

    public function setParentComission(float $parentComission): static
    {
        $this->parentComission = $parentComission;

        return $this;
    }

    public function getMonthlyConsumption(): ?float
    {
        return $this->monthlyConsumption;
    }

    public function setMonthlyConsumption(float $monthlyConsumption): static
    {
        $this->monthlyConsumption = $monthlyConsumption;

        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getTeamMonthlyConsumption(): ?float
    {
        return $this->teamMonthlyConsumption;
    }

    public function setTeamMonthlyConsumption(float $teamMonthlyConsumption): static
    {
        $this->teamMonthlyConsumption = $teamMonthlyConsumption;

        return $this;
    }

}
