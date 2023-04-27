<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Ville;
use App\Form\CampusForm;
use App\Form\CreationFormType;
use App\Form\CreationCSVFormType;
use App\Form\ParticipantsFormType;
use App\Form\SearchForm;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use App\Services\TraitementParticipantCSV;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/admin", name="admin_",methods={"GET"})
 */
class AdminController extends AbstractController
{

    /**
     *
     * @Route("/gestion_campus", name="gestion_campus")
     */
    public function gestionCampus(
        Request $request,CampusRepository $campusRepository, EntityManagerInterface $entityManager):response{

//Creation du formulaire pour ajouter
        $campus = new Campus();
        $form = $this->createForm(CampusForm::class, $campus);
        $form->handleRequest($request);

//Creation du formulaire de la barre de recherche
        $search = new SearchData();
        $searchForm = $this->createForm(SearchForm::class,$search);
        $searchForm->handleRequest($request);

//Recherche des campus (si critères => dans variable search)
        $campusList=$campusRepository->findCampus($search);

//Ajout du nouveau campus dans la BDD
        if ($form->isSubmitted()) {

            if($form->isValid()){
                $this->addFlash('success', 'Campus : "'. $campus->getNom() .'" ajouté !');

                $entityManager->persist($campus);
                $entityManager->flush();}

            else{

                $this->addFlash('error', 'Le nom de campus "'. $campus->getNom() . '" existe déjà.');

            }

            return $this->redirectToRoute('admin_gestion_campus');
        }


        return $this->render('admin/gestion_campus.html.twig',['campus'=>$campusList,'CampusForm' => $form->createView(),'SearchForm' => $searchForm->createView()]);

    }
    /**
     *
     * @Route("/gestion_campus/modify/{id}", name="modify_campus")
     */
    public function modifyCampus(Request $request,EntityManagerInterface $entityManager,CampusRepository $campusRepository){

//récupère l'ID du campus à modifier
        $id = $request->get('id');
        $campus = new Campus() ;
        $campus = $campusRepository->find($id);

//création du form du campus choisi
        $form = $this->createForm(CampusForm::class, $campus);
        $form->handleRequest($request);
//création du formulaire de la barre recherche(useless ici mais pour l'esthétique, sinon elle n'apparait pas)
        $search = new SearchData();
        $searchForm = $this->createForm(SearchForm::class,$search);
        $searchForm->handleRequest($request);

//recuperation de la liste de tous les campus (l'objet $search est vide)
        $campusList=$campusRepository->findCampus($search);

//Modification du campus dans la bdd
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($campus);
            $entityManager->flush();

            return $this->redirectToRoute('admin_gestion_campus');
        }


        return $this->render('admin/gestion_campus.html.twig', [
            'CampusForm' => $form->createView(), 'campus'=>$campusList,'id'=>$id,'SearchForm' => $searchForm->createView()
        ]);

    }

    /**
     *
     * @Route("/gestion_campus/delete/{id}", name="delete_campus")
     */
    public function deleteCampus(Request $request,EntityManagerInterface $entityManager,CampusRepository $campusRepository){

        $id=$request->get('id');
        $campus=$campusRepository->find($id);
        $entityManager->remove($campus);
        $entityManager->flush();

        return $this->redirectToRoute('admin_gestion_campus');

    }

    /**
     * @Route("/creationParticipant", name="creationParticipant", methods={"POST"})
     */
    public function creationParticipant(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response

    {
        $participant = new Participant();
        $form = $this->createForm(CreationFormType::class, $participant);
        $formCSV = $this->createForm(CreationCSVFormType::class, $participant);
        $form->handleRequest($request);
        $formCSV->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setAdministrateur(false);
            $participant->setActif(true);
            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Félicitations! Un nouveau participant a été créé!');
            return $this->redirectToRoute('main_profil',['id' => $participant->getId()]);

        } elseif ($formCSV->isSubmitted() && $formCSV->get('csvFile')->getData()) { // Vérification si le fichier a été soumis
        // Traitement du fichier CSV

            $csvFile = $formCSV->get('csvFile')->getData();
            $traitement= new TraitementParticipantCSV($validator);
            $traitement->creationParticipantParFichier($csvFile,$entityManager);


            $this->addFlash('success', 'Félicitations! Les participants ont été créés à partir du fichier CSV!');


            return $this->redirectToRoute('admin_creationParticipant');
        }

        return $this->render('admin/creationParticipant.html.twig', [
            'creationForm' => $form->createView(),
            'creationFormCSV' => $formCSV->createView(),
        ]);
    }


    /**
     * @Route("/desactivation/{id}", name="desactivation", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function desactiverParticipant (EntityManagerInterface $entityManager, int $id): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);


        if ($participant->isActif()) {
            $participant->setActif(false);

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Le compte a été désactivé.');
        } else {
            $this->addFlash('error', 'Le compte est déjà inactif.');
        }

        return $this->redirectToRoute('admin_gestionParticipants');
    }

    /**
     * @Route("/reactivation/{id}", name="reactivation", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function reactiverParticipant (Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);

        $participant->setActif(true);

        $entityManager->persist($participant);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte a été réactivé.');

        return $this->redirectToRoute('admin_gestionParticipants');
    }

    /**
     * @Route("/suppression/{id}", name="suppression")
     */
    public function supprimerParticipant (
        Request $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository){

        $id=$request->get('id');
        $participant=$participantRepository->find($id);
        $entityManager->remove($participant);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte a été supprimé.');

        return $this->redirectToRoute('admin_gestionParticipants');
    }
    /**
     * GESTION DES VILLES
     * @Route("/gestion_ville", name="gestion_ville",methods={"POST"})
     */
    public function gestionVille(
        Request $request,VilleRepository $villeRepository, EntityManagerInterface $entityManager):response{

//Creation du formulaire pour ajouter
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);


//Creation du formulaire de la barre de recherche
        $search = new SearchData();
        $searchForm = $this->createForm(SearchForm::class,$search);
        $searchForm->handleRequest($request);

//Recherche des villes (si critère => dans variable search)
        $villeList=$villeRepository->findVille($search);

//Ajout d'une nouvelle ville dans la BDD
        if ($form->isSubmitted()) {

            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('success', 'La ville "' . $ville->getNom() . ' ('.$ville->getCodePostal().')" vient d\'être créée.');

            return $this->redirectToRoute('admin_gestion_ville');
        }


        return $this->render('admin/gestion_ville.html.twig',['ville'=>$villeList,'villeForm' => $form->createView(),'SearchForm' => $searchForm->createView()]);

    }
    /**
     * MODIFICATION D UNE VILLE
     * @Route("/gestion_ville/modify/{id}", name="modify_ville",methods={"POST|GET"})
     */
    public function modifyVille(Request $request,EntityManagerInterface $entityManager,VilleRepository $villeRepository){

//récupère l'ID de la ville à modifier
        $id = $request->get('id');
        $ville = new Ville() ;
        $ville = $villeRepository->find($id);

        $ancienneVille = clone $ville;
//création du form de la ville choisie
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);
//création du formulaire de la barre recherche(useless ici mais pour l'esthétique, sinon elle n'apparait pas)
        $search = new SearchData();
        $searchForm = $this->createForm(SearchForm::class,$search);
        $searchForm->handleRequest($request);

//recuperation de la liste de toutes les villes (l'objet $search est vide)
        $villeList=$villeRepository->findVille($search);
//Modification du campus dans la bdd
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('success', 'La ville "' . $ancienneVille->getNom() . ' ('.$ancienneVille->getCodePostal().')" vient d\'être remplacée par "' . $ville->getNom().' ('.$ville->getCodePostal().')".');

            return $this->redirectToRoute('admin_gestion_ville');
        }


        return $this->render('admin/gestion_ville.html.twig', [
            'villeForm' => $form->createView(), 'ville'=>$villeList,'id'=>$id,'SearchForm' => $searchForm->createView()
        ]);

    }

    /**
     * SUPPRESSION D UNE VILLE
     * @Route("/gestion_ville/delete/{id}", name="delete_ville")
     */
    public function deleteVille(Request $request,EntityManagerInterface $entityManager,VilleRepository $villeRepository){

        $id=$request->get('id');
        $ville=$villeRepository->find($id);
        $entityManager->remove($ville);
        $entityManager->flush();

        $this->addFlash('success', 'La ville "' . $ville->getNom() . ' ('.$ville->getCodePostal().')" vient d\'être supprimée.');


        return $this->redirectToRoute('admin_gestion_ville');

    }



    /**
     * @Route("/gestionParticipants", name="gestionParticipants")
     */
    public function gestionParticipants(
        Request $request,
        ParticipantRepository $participantRepository
    ):response{

//Creation du formulaire de la barre de recherche
        $search = new SearchData();
        $searchForm = $this->createForm(SearchForm::class,$search);
        $searchForm->handleRequest($request);

//Recherche des participants (si critères => dans variable search)
        $listeParticipants=$participantRepository->findParticipants($search);


        return $this->render('admin/gestion_participants.html.twig',[
            'participants'=>$listeParticipants,
            'SearchForm' => $searchForm->createView()]);

    }
}