<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Participant;
use App\EtatControle\VerificationDate;
use App\Form\ProfilChangePasswordFormType;
use App\Form\ProfileType;
use App\Form\SearchForm;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;


class MainController extends AbstractController
{

    /**
     * @Route("/", name="main_home", methods={"GET"})
     */
    public function home(SortieRepository $sortieRepository, Request $request, EtatRepository $etatRepository, EntityManagerInterface $entityManager, VerificationDate $verificationDate): Response {

        // Récupération du User en cours, si non connecté renvoi à la page de connexion

        $user = $this->getUser();

        if ($user==null) {

            return $this->redirectToRoute("app_login");
        }

        //Création d'un objet de type SearchData qui modélise le formulaire
        $data = new SearchData();
        $form = $this->createForm(SearchForm::class,$data);
        $form->handleRequest($request);

        //Envoi de l'objet SearchData et du User en paramètre à la fonction de recherche des sorties
        $sortie = $sortieRepository->findSorties($data,$user);
        $verificationDate->verifSortieEtat($sortie, $etatRepository, $entityManager);

        //Envoi des sorties à la vue
        return $this->render('main/home.html.twig', ['sorties'=>$sortie,'form'=>$form->createView()]);    }

    /**
     * @Route("/admin/villes", name="main_villes")
     */
    public function villes(): Response
    {
        return $this->render('main/villes.html.twig');
    }

    /**
     * @Route("/admin/campus", name="main_campus")
     */
    public function campus(): Response
    {
        return $this->render('main/campus.html.twig');
    }

    /**
     * @Route("/profil/{id}", name="main_profil", requirements={"id"="\d+"})
     */
    public function profil(Request $request,
                           Security $security,
                           EntityManagerInterface $entityManager,
                           SluggerInterface $slugger,
                           UserPasswordHasherInterface $userPasswordHasher,
                           int $id

    ): Response
    {
        $participant = $security->getUser();

        // Vérifie si l'utilisateur connecté ≠ profil sélectionné
        if ($id !== $participant->getId()) {
            // Récupère l'id de l'autre profil
            $participant = $entityManager->getRepository(Participant::class)->find($id);
            if (!$participant) {
                throw $this->createNotFoundException("Ce participant n'existe pas");
            }
        }

        $participantForm = $this->createForm(ProfileType::class, $participant);
        $mdpForm = $this->createForm(ProfilChangePasswordFormType::class, $participant);
        $participantForm->handleRequest($request);
        $mdpForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()  ) {
            // Vérifie si le mot de passe fourni correspond au mot de passe actuel de l'utilisateur
            if (!$userPasswordHasher->isPasswordValid($participant,$participantForm->get('plainPassword')->getData())) {
                $this->addFlash('authentication_error', 'Le mot de passe est incorrect.');
                return $this->redirectToRoute('main_profil', ['id' => $participant->getId()]);
            }else {
                $image = $participantForm->get('nomImage')->getData();
                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                if ($image) {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $image->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $user = new Participant();
                    if ($participant instanceof $user) {
                        $participant->setNomImage($newFilename);
                    }
                }

                $entityManager->persist($participant);
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a bien été modifié!');
                return $this->redirectToRoute('main_home');
            }
            //Cas de soumission du formulaire de modification du mot de passe
        } elseif ($mdpForm->isSubmitted() && $mdpForm->isValid()) {
            if ($userPasswordHasher->isPasswordValid($participant, $mdpForm->get('currentPassword')->getData())) {
                $participant->setPassword(
                    $userPasswordHasher->hashPassword(
                        $participant,
                        $mdpForm->get('newPassword')->getData()
                    )
                );


                $this->addFlash('success', 'Le mot de passe a été modifié.');
                $entityManager->persist($participant);
                $entityManager->flush();
                return $this->redirectToRoute('main_profil', ['id' => $participant->getId()]);

            } else {
                $this->addFlash('authentication_error', 'Le mot de passe renseigné est incorrect.');

            }
        }

        return $this->render('main/profil.html.twig', [
            'ProfileForm' => $participantForm->createView(),
            'MdpForm' => $mdpForm ->createView(),
            'participant' => $participant]);
    }


}