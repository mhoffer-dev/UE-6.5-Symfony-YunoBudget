<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Utilisateur;
use App\Enum\TypeCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /** @return Categorie[] */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Categorie[] */
    public function findByUtilisateurAndType(Utilisateur $utilisateur, TypeCategorie $type): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur = :utilisateur')
            ->andWhere('c.type = :type')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}