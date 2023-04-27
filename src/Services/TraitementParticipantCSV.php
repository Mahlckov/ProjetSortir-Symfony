<?php

namespace App\Services;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TraitementParticipantCSV
{
//mise en place du système de validation
    public $validator;


    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }
    public function creationParticipantParFichier(string $csvFile, EntityManagerInterface $entityManager): void
    {

        $fichier = new \SplFileObject($csvFile);
        $fichier->setFlags(\SplFileObject::READ_CSV);

        $entete = true; //on teste s'il y a une en-tete dans le fichier
        foreach ($fichier as $col) {
            if ($entete) {
                if ($col[0] === 'Nom') {
                    if ($col[1] === 'Prenom') { //si on détecte dans les 2 premieres cases Nom Prenom = entete = saut de ligne
                        $entete = false; //on considère les lignes suivantes comme pas des entetes
                    }
                    continue;
                } else {
                    $entete = false; //pas d'en-tete
                }
            }

        // vérifie si la ligne est vide
            if (empty($col[0])) {
                break;
            }

            $participant = new Participant();
            $participant->setNom($col[0]);
            $participant->setPrenom($col[1]);
            $participant->setEmail($col[2]);
            $participant->setPseudo($col[3]);
            $participant->setPassword($col[4]);
            $participant->setTelephone($col[5]);
            $participant->setAdministrateur(false);
            $participant->setActif(true);

            $campus = $entityManager->getRepository(Campus::class)
                ->findOneBy(['nom' => $col[6]]);
            if (!$campus) {
                throw new \RuntimeException(sprintf(
                    'Impossible de récupérer le Campus de nom "%s"', $col[6]
                ));
            }
            $participant->setCampus($campus);

            // validation du participant avant la persistance des données
            $errors = $this->validator->validate($participant);
            if (count($errors) > 0) {
                throw new \RuntimeException((string)$errors);
            }
            $participant->setPassword(password_hash($col[4], PASSWORD_DEFAULT));

            $entityManager->persist($participant);
        }

        $entityManager->flush();
    }
}