<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\DateType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function add(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSortieId(int $id)
    {
        $query = $this->createQueryBuilder('s')
            ->select('s','orga','lieu','etat','participants','ville', 'campus', 'inscriptions')
            ->leftJoin('s.organisateur', 'orga')
            ->leftJoin('s.lieu','lieu')
            ->leftJoin('s.etat','etat')
            ->leftJoin('s.participants','participants')
            ->leftJoin('lieu.ville','ville')
            ->leftJoin('s.campus', 'campus')
            ->leftJoin('participants.inscriptions', 'inscriptions');

        $query->andWhere('s.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getResult();
    }

    public function findSorties(SearchData $data,Participant $user){
        //listALL = si les deux options inscrit et non inscrits sont cochées simultanément, par defaut = false;
        $listAll = false;
        $query = $this->createQueryBuilder('s')
                        ->select('s','orga','lieu','etat','participants','ville')
                        ->leftJoin('s.organisateur', 'orga')
                        ->leftJoin('s.lieu','lieu')
                        ->leftJoin('s.etat','etat')
                        ->leftJoin('s.participants','participants')
                        ->leftJoin('lieu.ville','ville');


        //Si le paramètre de "recherche" est rempli
        if(!empty($data->q))
        {
           $query = $query
               ->andWhere('s.nom LIKE :q')
               ->setParameter('q',"%{$data->q}%");

        }

        //Si le paramètre de "campus" est rempli

        if(!empty($data->campus))
        {
            $query = $query
                ->andWhere('s.campus IN (:campus)')
                ->setParameter('campus', $data->campus);

        }
        //Si le paramètre de "dateMin" est rempli

        if(!empty($data->dateMin))
        {
            $query = $query
                ->andWhere('s.dateHeureDebut >= :dateMin')
                ->setParameter('dateMin', $data->dateMin);

        }
        //Si le paramètre de "dateMax" est rempli

        if(!empty($data->dateMax))
        {
            $query = $query
                ->andWhere('s.dateHeureDebut <= :dateMax')
                ->setParameter('dateMax', $data->dateMax);

        }

        //Si le paramètre "organisateur" est rempli


        if(!empty($data->organisateur))
        {   $currentUser = $user->getId();
            $query = $query
                ->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $currentUser);

        }

        //Si le paramètre de "sortie terminée" est rempli


        if(!empty($data->sortiesTerminees))
        {   $date = new \DateTime();
            $query = $query
                ->andWhere('s.dateHeureDebut < :sortieTerminee')
                ->setParameter('sortieTerminee', $date);

        }

        //Si les paramètres "inscrit" et "non inscrit" sont tous les deux remplis alors le boolean pour ne pas effectuer
        // de recherche selon ces paramètres = true

        if((!empty($data->inscrit))&&(!empty($data->nonInscrit)))
        {$listAll=true;}


        //Si le paramètre "inscrit" est rempli et "non inscrit" n'est pas rempli

        if((!empty($data->inscrit))&& $listAll==false)
        {   $currentUser = $user->getInscriptions();
            $query = $query
                ->andWhere('s.id IN (:inscrit)')
                ->setParameter('inscrit', $currentUser);

        }


        //Si le paramètre "non inscrit" est rempli et "inscrit" n'est pas rempli

        if((!empty($data->nonInscrit))&& $listAll==false)
        {   $currentUser = $user->getInscriptions();
            $query = $query
                ->andWhere('s.id NOT IN (:inscrit)')
                ->setParameter('inscrit', $currentUser);

        }
        $query = $query->addOrderBy('s.id', 'DESC');


        return $query->getQuery()->getResult();
    }


//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
