<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/new/{id?}", name="nouvelle_sortie", requirements={"id"="\d+"})
     */
    public function new(Request                $request,
                        EntityManagerInterface $entityManager,
                        EtatRepository         $etatRepository,
                        SortieRepository       $sortieRepository,
                                               $id = 0): Response
    {
        $afficher = 0;
        $modifier = 0;
        $listeParticipant = null;
        $campus = $this->getUser()->getCampus();

        if ($id == 0) {
            $sortie = new Sortie();
            $sortie->setCampus($campus);
            $sortie->setOrganisateur($this->getUser());
        } else {
            $sortieFind = $sortieRepository->findSortieId($id);

            //redirection si l'id de l'url n'existe pas
            if (count($sortieFind) == 0) {
                return $this->redirectToRoute('main_home');
            }

            $sortie = $sortieFind[0];
            if ($sortie->getOrganisateur() !== $this->getUser()) {
                $afficher = 1;
            } else {
                $modifier = 1;
            }
            if ($sortie->getEtat()->getId() != 1 && $sortie->getEtat()->getId() != 2 && $sortie->getEtat()->getId() != 3) {
                $afficher = 1;
            } else {
                $modifier = 1;
            }
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('nouvelle_sortie');
        }

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('nouvelle_sortie');
        }

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if ($sortieForm->get('enregistrer')->isClicked()) {
                if ($sortie->getEtat() === null) {
                    $creee = 1;
                    $etat = $etatRepository->find($creee);
                    $sortie->setEtat($etat);
                    $this->addFlash('success', 'Sortie enregistrée !');
                } else {
                    $this->addFlash('success', 'Modifications enregistrées !');
                }


            } elseif ($sortieForm->get('publier')->isClicked()) {
                if ($sortie->getEtat()->getId() != 2) {
                    $publiee = 2;
                    $etat = $etatRepository->find($publiee);
                    $sortie->setEtat($etat);
                    $this->addFlash('success', 'Sortie publiée !');
                }else{
                    $this->addFlash('success', 'Modifications enregistrées !');
                }
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/new.html.twig', [
            "sortieForm" => $sortieForm->createView(),
            "lieuForm" => $lieuForm->createView(),
            "villeForm" => $villeForm->createView(),
            'campus' => $campus,
            'sortie' => $sortie,
            'afficher' => $afficher,
            'modifier' => $modifier,
            'listeParticipant' => $listeParticipant
        ]);
    }

    /**
     * @Route("/sortie/annuler/{id}", name="supprimer_sortie", requirements={"id"="\d+"})
     */
    function annuler($id,
                     EntityManagerInterface $entityManager,
                     SortieRepository $sortieRepository,
                     EtatRepository $etatRepository
    ): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            return $this->redirectToRoute('main_home');
        }
        $etatSortie = $sortie->getEtat()->getId();
        if ($etatSortie == 1 || $etatSortie == 2 || $etatSortie == 3) {

            if ($sortie->getOrganisateur()->getId() == $this->getUser()->getId()) {

                if (isset($_POST['annulation'])) {
                    $sortie->setEtat($etatRepository->find(6));
                    $sortie->setInfosSortie($_POST['motif']);
                    $entityManager->persist($sortie);
                    $this->addFlash('success', 'Sortie annulée !');
                    $entityManager->flush();

                    return $this->redirectToRoute('main_home');
                } else {
                    return $this->render('sortie/annulation.html.twig', [
                        "sortie" => $sortie
                    ]);
                }

            } else {
                return $this->redirectToRoute('main_home');
            }

        } else {
            return $this->redirectToRoute('main_home');
        }

    }

    /**
     * @Route("/sortie/publier/{id}", name="publier_sortie", requirements={"id"="\d+"})
     */
    function publier($id,
                     EntityManagerInterface $entityManager,
                     SortieRepository $sortieRepository,
                     EtatRepository $etatRepository
    ): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            return $this->redirectToRoute('main_home');
        }

        if ($sortie->getEtat()->getId() == 1 && $this->getUser()->getId() == $sortie->getOrganisateur()->getId()) {
            $etat = $etatRepository->find(2);
            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main_home');
    }

    /**
     * @Route("/sortie/inscription/{id}", name="inscription_sortie", requirements={"id"="\d+"})
     */
    function inscription($id,
                         EntityManagerInterface $entityManager,
                         SortieRepository $sortieRepository,
                         ParticipantRepository $participantRepository
    ): Response
    {

        $participant = $participantRepository->find($this->getUser()->getId());

        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            return $this->redirectToRoute('main_home');
        }

        if ($sortie->getEtat()->getId() == 2 && $participant->getId() !== $sortie->getOrganisateur()->getId()) {

            $participant->addInscription($sortie);
            $sortie->addParticipant($participant);

            $entityManager->persist($participant);
            $entityManager->persist($sortie);
            $entityManager->flush();

        }

        return $this->redirectToRoute('main_home');
    }

    /**
     * @Route("/sortie/desister/{id}", name="desister_sortie", requirements={"id"="\d+"})
     */
    function desister($id,
                      EntityManagerInterface $entityManager,
                      SortieRepository $sortieRepository,
                      ParticipantRepository $participantRepository
    ): Response
    {

        $participant = $participantRepository->find($this->getUser()->getId());

        $sortie = $sortieRepository->find($id);

        if ($sortie == null) {
            return $this->redirectToRoute('main_home');
        }

        if ($sortie->getEtat()->getId() == 2 && $participant->getId() !== $sortie->getOrganisateur()->getId()) {

            $participant->removeInscription($sortie);
            $sortie->removeParticipant($participant);

            $entityManager->persist($participant);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main_home');
    }
}

