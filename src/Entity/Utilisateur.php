<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $motDePasse = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateInscription = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Transaction::class, orphanRemoval: true)]
    private Collection $transactions;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: MoyenPaiement::class, orphanRemoval: true)]
    private Collection $moyenPaiements;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Categorie::class, orphanRemoval: true)]
    private Collection $categories;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->moyenPaiements = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->dateInscription = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getMotDePasse(): ?string { return $this->motDePasse; }
    public function setMotDePasse(string $motDePasse): static { $this->motDePasse = $motDePasse; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDateInscription(): ?\DateTimeImmutable { return $this->dateInscription; }
    public function setDateInscription(\DateTimeImmutable $dateInscription): static { $this->dateInscription = $dateInscription; return $this; }

    /** @return Collection<int, Transaction> */
    public function getTransactions(): Collection { return $this->transactions; }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setUtilisateur($this);
        }
        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction) && $transaction->getUtilisateur() === $this) {
            $transaction->setUtilisateur(null);
        }
        return $this;
    }

    /** @return Collection<int, MoyenPaiement> */
    public function getMoyenPaiements(): Collection { return $this->moyenPaiements; }

    public function addMoyenPaiement(MoyenPaiement $moyenPaiement): static
    {
        if (!$this->moyenPaiements->contains($moyenPaiement)) {
            $this->moyenPaiements->add($moyenPaiement);
            $moyenPaiement->setUtilisateur($this);
        }
        return $this;
    }

    public function removeMoyenPaiement(MoyenPaiement $moyenPaiement): static
    {
        if ($this->moyenPaiements->removeElement($moyenPaiement) && $moyenPaiement->getUtilisateur() === $this) {
            $moyenPaiement->setUtilisateur(null);
        }
        return $this;
    }

    /** @return Collection<int, Categorie> */
    public function getCategories(): Collection { return $this->categories; }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categories->contains($categorie)) {
            $this->categories->add($categorie);
            $categorie->setUtilisateur($this);
        }
        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        if ($this->categories->removeElement($categorie) && $categorie->getUtilisateur() === $this) {
            $categorie->setUtilisateur(null);
        }
        return $this;
    }
}