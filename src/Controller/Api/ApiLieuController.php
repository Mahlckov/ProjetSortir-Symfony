<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiLieuController extends AbstractController
{

    /**
     * @Route("/api/ville", name="api_ville_liste", methods={"GET"})
     */
    public function ville(EntityManagerInterface $entityManager): JsonResponse
    {
        $dql = "SELECT v FROM App\Entity\Ville v";

        $query = $entityManager->createQuery($dql);
        $ville =  $query->getResult();

        return $this->json($ville, Response::HTTP_OK,[], ['groups' => 'liste_ville']);
    }

    /**
     * @Route("/api/lieux/{id}", name="api_lieux_liste", methods={"GET"})
     */
    public function lieu(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $dql = "SELECT l FROM App\Entity\Lieu l 
		WHERE l.ville = :idVille";

        $query = $entityManager->createQuery($dql);
        $query->setParameter('idVille', $id);
        $lieux =  $query->getResult();

        return $this->json($lieux, Response::HTTP_OK,[], ['groups' => 'liste_lieux']);
    }

    /**
     * @Route("/api/lieux/details/{id}", name="api_lieu_details", methods={"GET"})
     */
    public function detailsLieu(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $dql = "SELECT l FROM App\Entity\Lieu l WHERE l.id = :id";
        $query = $entityManager->createQuery($dql);
        $query->setParameter('id', $id);
        $ville =  $query->getResult();

        return $this->json($ville, Response::HTTP_OK,[], ['groups' => 'liste_lieux']);
    }

}