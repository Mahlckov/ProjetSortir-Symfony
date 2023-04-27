<?php

namespace App\EtatControle;

use DateTime;

class VerificationDate
{
    function verifSortieEtat($listeSortie, $etatRepository, $entityManager):void
    {
        foreach($listeSortie as $sortie ){
            $dateHeureDebut = $sortie->getDateHeureDebut();
            if($sortie->getEtat()->getId() != 6 && $sortie->getEtat()->getId() != 5 && $sortie->getEtat()->getId() != 7) {
                $dateFinInscription = $sortie->getDateLimiteInscription();
                $duree = $sortie->getDuree();
                $this->verifFinInscription($dateFinInscription, $sortie, $etatRepository, $entityManager);
                $this->verifDebut($dateHeureDebut, $duree, $sortie, $etatRepository, $entityManager);
            }else{
                $this->verifArchivage($dateHeureDebut, $sortie, $etatRepository, $entityManager);
            }
        }
    }

    function verifDebut($dateDebut, $duree, $sortie, $etatRepository, $entityManager):void
    {
        $dateTime = new DateTime();
        $intervalDebut = $dateDebut->format('U') - $dateTime->format('U');
        $dateFinSortie = $dateDebut->modify("+$duree minutes");
        $intervalFin = $dateFinSortie->format('U') - $dateTime->format('U');
        $dateDebut->modify("-$duree minutes");

        if($intervalDebut <= 0){
            if($intervalFin >= 0){
                if($sortie->getEtat()->getId() != 4) {
                    $etat = $etatRepository->find(4);
                    $sortie->setEtat($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                }
            }else{
                if($sortie->getEtat()->getId() != 5) {
                    $etat = $etatRepository->find(5);
                    $sortie->setEtat($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                }
            }
        }
    }

    function verifFinInscription($dateFinInscr, $sortie, $etatRepository, $entityManager):void
    {
        $dateTime = new DateTime();
        $interval = $dateFinInscr->format('U') - $dateTime->format('U');
        if($interval <= 0){
            if($sortie->getEtat()->getId() == 1 || $sortie->getEtat()->getId() == 2){
                $etat = $etatRepository->find(3);
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();
            }
        }
    }

    private function verifArchivage($dateDebut, $sortie, $etatRepository, $entityManager):void
    {
        $dateTime = new DateTime();
        $intervalDebut = $dateTime->format('U') - $dateDebut->format('U');
        if($intervalDebut > 2629800){
            $etat = $etatRepository->find(7);
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
    }


}