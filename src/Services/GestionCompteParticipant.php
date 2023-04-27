<?php

namespace App\Services;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;

class GestionCompteParticipant
{
    public function setActifParticipant(EntityManagerInterface $entityManager, int $id, bool $actif): void
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);

        if ($participant) {
            if ($participant->isActif() !== $actif) {
                $participant->setActif($actif);
                $entityManager->persist($participant);
                $entityManager->flush();
            }
        }
    }
}