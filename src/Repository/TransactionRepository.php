<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /** @return Transaction[] */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('t.dateTransaction', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** Pour le bilan annuel : total par catégorie sur une année donnée */
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