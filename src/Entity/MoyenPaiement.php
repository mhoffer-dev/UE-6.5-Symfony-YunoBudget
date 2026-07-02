<?php

namespace App\Entity;

use App\Enum\TypePaiement;
use App\Repository\MoyenPaiementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoyenPaiementRepository::class)]
class MoyenPaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroMasque = null;

    #[ORM\Column(length: 20, enumType: TypePaiement::class)]
    private ?TypePaiement $type = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $libelleBanque = null;

    #[ORM\ManyToOne(inversedBy: 'moyenPaiements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'moyenPaiement', targetEntity: Transaction::class)]
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getNumeroMasque(): ?string { return $this->numeroMasque; }
    public function setNumeroMasque(?string $numeroMasque): static
    {
        if ($numeroMasque) {
            // 1. On nettoie les espaces ou tirets éventuels (ex: "4571 1234..." -> "45711234...")
            $cleanNumber = str_replace([' ', '-'], '', $numeroMasque);
            
            // 2. On récupère la longueur du numéro
            $length = strlen($cleanNumber);

            if ($length > 4) {
                // On garde les 4 derniers chiffres et on met des '*' devant
                $derniersChiffres = substr($cleanNumber, -4);
                $this->numeroMasque = str_repeat('*', $length - 4) . $derniersChiffres;
            } else {
                // Si le numéro est très court (ex: un RIB partiel), on le laisse tel quel
                $this->numeroMasque = $cleanNumber;
            }
        } else {
            $this->numeroMasque = null;
        }

        return $this;
    }

    public function getType(): ?TypePaiement { return $this->type; }
    public function setType(TypePaiement $type): static { $this->type = $type; return $this; }

    public function getLibelleBanque(): ?string { return $this->libelleBanque; }
    public function setLibelleBanque(?string $libelleBanque): static { $this->libelleBanque = $libelleBanque; return $this; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    /** @return Collection<int, Transaction> */
    public function getTransactions(): Collection { return $this->transactions; }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setMoyenPaiement($this);
        }
        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction) && $transaction->getMoyenPaiement() === $this) {
            $transaction->setMoyenPaiement(null);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}