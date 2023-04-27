<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Form\GroupType;
use App\Repository\GroupeRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    /**
     * @Route("/groups/create", name="app_create_group")
     */
    public function create(Request $request, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository):Response
    {
        $groupe = new Groupe();

        $form = $this->createForm(GroupType::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupe->setIdCreateur($this->getUser());
            $entityManager->persist($groupe);
            $entityManager->flush();
            $this->addFlash('success', 'Le groupe ' . $groupe->getName() .'a bien été créé!');


            // redirect to the group listing page
            return $this->redirectToRoute('app_list_groups');
        }

        return $this->render('group/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/groups", name="app_list_groups", methods={"GET"})
     */
    public function listGroups(GroupeRepository $groupRepository)
    {
        $id=$this->getUser()->getId();
        $groups = $groupRepository->findGroups($id);


        return $this->render('group/list.html.twig', [
            'groups' => $groups,
        ]);

    }




    /**
     * @Route("/group/edit/{id}", name="group_edit")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, GroupeRepository $groupeRepository):Response
    {


        $id = $request->get('id');

        $group = $groupeRepository->find($id);

        if (!$group) {
            throw $this->createNotFoundException("Ce groupe n'existe pas");
        }

        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


                    $entityManager->persist($group);

                    $entityManager->flush();

                    $this->addFlash('success', 'Groupe ' . $group->getName() .'modifié avec succès!');

                return $this->redirectToRoute('app_list_groups');
            }


            return $this->render('group/edit.html.twig', [
                'group' => $group,
                'form' => $form->createView(),
            ]);

    }



    /**
     * @Route("/group/{id}", name="group_delete")
     */
    public function delete(Request $request, Groupe $group, EntityManagerInterface $entityManager, GroupeRepository $groupeRepository): Response
    {
        $id=$request->get('id');
        $group=$groupeRepository->find($id);
        $entityManager->remove($group);
        $entityManager->flush();

        $this->addFlash('success', 'Le groupe "' . $group->getName() .'" vient d\'être supprimé.');

        return $this->redirectToRoute('app_list_groups');
    }
}