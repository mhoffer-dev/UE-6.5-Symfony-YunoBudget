<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Récupère toutes les transactions d'un utilisateur par ordre chronologique décroissant.
     * @return Transaction[]
     */
    public function findByUtilisateur(UserInterface $utilisateur): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('t.dateTransaction', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avancée et filtrage des transactions (Sécurisé contre les injections SQL via QueryBuilder).
     *
     * @param UserInterface $utilisateur L'utilisateur connecté (isolation stricte des données)
     * @param string|null $search Mot-clé dans le libellé de la transaction
     * @param int|null $categorieId Identifiant de la catégorie filtrée
     * @param string|null $type Type de flux ('REVENU' ou 'DEPENSE')
     * @return Transaction[]
     */
    public function findWithFilters(UserInterface $utilisateur, ?string $search = null, ?int $categorieId = null, ?string $type = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur);

        // 1. Filtre par mot-clé (Recherche dans le libellé)
        if (!empty($search)) {
            $qb->andWhere('t.libelleTransaction LIKE :search')
               ->setParameter('search', '%' . trim($search) . '%');
        }

        // 2. Filtre par Catégorie
        if (!empty($categorieId) && $categorieId > 0) {
            $qb->andWhere('t.categorie = :catId')
               ->setParameter('catId', $categorieId);
        }

        // 3. Filtre par Type de flux (Revenu > 0 ou Dépense < 0)
        if ($type === 'REVENU') {
            $qb->andWhere('t.montant > 0');
        } elseif ($type === 'DEPENSE') {
            $qb->andWhere('t.montant < 0');
        }

        return $qb->orderBy('t.dateTransaction', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Pour le bilan annuel : total par catégorie sur une année donnée pour l'utilisateur.
     */
    public function getBilanAnnuelParCategorie(Utilisateur $utilisateur, int $annee): array
    {
        return $this->createQueryBuilder('t')
            ->select('c.nom AS categorie', 'c.type AS type', 'SUM(t.montant) AS total')
            ->join('t.categorie', 'c')
            ->andWhere('t.utilisateur = :utilisateur')
            ->andWhere('YEAR(t.dateTransaction) = :annee')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('annee', $annee)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}