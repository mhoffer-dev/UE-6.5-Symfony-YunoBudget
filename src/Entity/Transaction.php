<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateTransaction = null;

    #[ORM\Column(length: 255)]
    private ?string $libelleTransaction = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MoyenPaiement $moyenPaiement = null;

    private ?utilisateur $user = null;

    public function getId(): ?int { return $this->id; }

    public function getMontant(): ?float { return $this->montant; }
    public function setMontant(float $montant): static { $this->montant = $montant; return $this; }

    public function getDateTransaction(): ?\DateTimeImmutable { return $this->dateTransaction; }
    public function setDateTransaction(\DateTimeImmutable $dateTransaction): static { $this->dateTransaction = $dateTransaction; return $this; }

    public function getLibelleTransaction(): ?string { return $this->libelleTransaction; }
    public function setLibelleTransaction(string $libelleTransaction): static { $this->libelleTransaction = $libelleTransaction; return $this; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getCategorie(): ?Categorie { return $this->categorie; }
    public function setCategorie(?Categorie $categorie): static { $this->categorie = $categorie; return $this; }

    public function getMoyenPaiement(): ?MoyenPaiement { return $this->moyenPaiement; }
    public function setMoyenPaiement(?MoyenPaiement $moyenPaiement): static { $this->moyenPaiement = $moyenPaiement; return $this; }

}