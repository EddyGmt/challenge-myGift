<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\User;
use App\Form\ListeType;
use App\Repository\ListeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/liste')]
class ListeController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_liste_index', methods: ['GET'])]
    public function index(ListeRepository $listeRepository): Response
    {
        return $this->render('liste/index.html.twig', [
            'listes' => $listeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_liste_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ListeRepository $listeRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isIsActive()) {
            return $this->render('resend_verif');
        } else {
            $liste = new Liste();
            $form = $this->createForm(ListeType::class, $liste);
            $form->handleRequest($request);

            $dateOuverture = $liste->setDateOuveture(new \DateTime());
            $liste->setUserId($this->getUser());

            if ($form->isSubmitted() && $form->isValid()) {
                $listeRepository->save($liste, true);

                return $this->redirectToRoute('app_my_list', [], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('liste/new.html.twig', [
                'liste' => $liste,
                'form' => $form,
            ]);
        }
    }

    //TODO faire apparaitre les gifts qui lui sont attribué également
    #[Route('/mes-listes', name: 'app_my_list', methods: ['GET'])]
    public function myList(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $listes = $user->getListeId();

        return $this->render('liste/mylist.html.twig', [
            'listes' => $listes,
        ]);
    }

    //TODO faire une méthode permettant d'archiver les listes
    #[Route('archive-liste/{id}', name: 'archive_liste', methods: ['POST'])]
    public function archiveListe(Request $request, $id): Response
    {

    }


    #[Route('/{id}', name: 'app_liste_show', methods: ['GET'])]
    public function show(Liste $liste): Response
    {
        $gift = $liste->getGiftId();

        return $this->render('liste/show.html.twig', [
            'liste' => $liste,
            'gift' => $gift
        ]);
    }

    #[Route('/{id}/edit', name: 'app_liste_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Liste $liste, ListeRepository $listeRepository): Response
    {
        $form = $this->createForm(ListeType::class, $liste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listeRepository->save($liste, true);

            return $this->redirectToRoute('app_liste_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('liste/edit.html.twig', [
            'liste' => $liste,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_liste_delete', methods: ['POST'])]
    public function delete(Request $request, Liste $liste, ListeRepository $listeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $liste->getId(), $request->request->get('_token'))) {
            $listeRepository->remove($liste, true);
        }

        return $this->redirectToRoute('app_liste_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/ajout-cadeau', name: 'app_add_gift', methods: ["POST"])]
    public function showGifts($listeId): Response
    {
        // Récupérer la liste spécifique par son ID
        $liste = $this->getDoctrine()->getRepository(Liste::class)->find($listeId);

        if (!$liste) {
            // Gérer le cas où la liste n'est pas trouvée
            // Par exemple, rediriger vers une page d'erreur
            return $this->redirectToRoute('page_erreur');
        }

        // Récupérez les cadeaux liés à cette liste
        $gifts = $liste->getGifts();

        return $this->render('liste/show_gifts.html.twig', [
            'liste' => $liste,
            'gifts' => $gifts,
        ]);
    }

    #[Route('/{id/share', name: 'list_share', methods: ['GET'])]
    public function listShare(Request $request, $id): Response
    {
        $sentitymanager = $this->getDoctrine()->getManager();

        $liste = $sentitymanager->getRepository(Liste:: class)->find($id);
        if (!$liste) {
            throw $this->createNotFoundException('Liste non trouvée pour l\'ID ' . $id);
        }

        // Générez un lien de partage en fonction de l'ID de la liste
        $shareLink = $this->generateUrl('app_liste_show', [
            'id' => $liste->getId(),
        ], true); // true pour générer une URL absolue

        return $this->render('liste/share.html.twig', [
            'liste' => $liste,
            'shareLink' => $shareLink,
        ]);
    }

    #[Route('/{id}/archive-live', name: 'archive_liste', methods:['POST'])]
    public function archiveList(Request $request, Liste $liste):Response
    {
        $liste->setIsArchived(true);

        // Enregistrez les modifications en base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('liste/mylist.html.twig');

//        return render('liste/archive.html.twig',[
//
//        ]);

    }
}
