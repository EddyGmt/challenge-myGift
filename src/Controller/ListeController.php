<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\User;
use App\Form\ListeType;
use App\Repository\ListeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
            $liste->setIsArchived(false);

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

    #[Route('/mes-listes', name: 'app_my_list', methods: ['GET'])]
    public function myList(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $listes = $user->getListeId()->filter(function ($liste) {
            return !$liste->isIsArchived();
        });

        return $this->render('liste/mylist.html.twig', [
            'listes' => $listes,
        ]);
    }


    #[Route('/{id}', name: 'app_liste_show', methods: ['GET'])]
    public function show(Liste $liste, Request $request): Response
    {
        // Vérifier si la liste est privée et si un mot de passe a été fourni et est correct
        if ($liste->isIsPrivate() && !$this->isAuthorized($request, $liste)) {
            return $this->redirectToRoute('app_mdp_list', ['id' => $liste->getId()]);
        }

        $gift = $liste->getGiftId();

        return $this->render('liste/show.html.twig', [
            'liste' => $liste,
            'gift' => $gift
        ]);
    }

// Méthode pour vérifier si le mot de passe a été fourni et est correct
    private function isAuthorized(Request $request, Liste $liste): bool
    {
        $motDePasseFourni = $request->getSession()->get('liste_' . $liste->getId() . '_mdp');
        return $motDePasseFourni === $liste->getPassword();
    }

    #[Route('/get-mdp/{id}', name: 'app_mdp_list', methods: ['GET', 'POST'])]
    public function getMdp(Liste $liste, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $motDePasseFourni = $request->request->get('mot_de_passe');

            if ($motDePasseFourni === $liste->getPassword()) {
                // Stocker le mot de passe dans la session pour les futures vérifications
                $request->getSession()->set('liste_' . $liste->getId() . '_mdp', $motDePasseFourni);
                return $this->redirectToRoute('app_liste_show', ['id' => $liste->getId()]);
            } else {
                $this->addFlash('error', 'Mot de passe incorrect');
            }
        }

        return $this->render('liste/getMdpListe.html.twig', [
            'liste' => $liste,
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
        $liste = $this->getDoctrine()->getRepository(Liste::class)->find($listeId);

        if (!$liste) {
            return $this->redirectToRoute('page_erreur');
        }

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
        ], true);

        return $this->render('liste/share.html.twig', [
            'liste' => $liste,
            'shareLink' => $shareLink,
        ]);
    }

    #[Route('/archive-liste/{id}', name: 'archive_liste', methods: ['POST', 'GET'])]
    public function archiveListe(Request $request, $id, EntityManagerInterface $entityManager, ListeRepository $listeRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Utilisation du repository injecté
        $liste = $listeRepository->find($id);

        if (!$liste) {
            throw $this->createNotFoundException('Liste introuvable');
        }

        $liste->setIsArchived(true);
        $entityManager->flush();

        return $this->redirectToRoute('liste/myArchivedList.html.twig');
    }

    #[Route('/unarchive-liste/{id}', name: 'unarchive_liste', methods: ['POST'])]
    public function unarchiveListe(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Supposons que vous ayez une entité ListeCadeau avec une propriété isArchived.
        //$entityManager = $this->getDoctrine()->getManager();
        $liste = $entityManager->getRepository(ListeCadeau::class)->find($id);

        if (!$liste) {
            throw $this->createNotFoundException('Liste introuvable');
        }

        $liste->setIsArchived(false);
        $entityManager->flush();

        return $this->redirectToRoute('liste/mylist.html.twig');
    }

/*    #[Route('/mes-listes-archivees', name: 'my_archived_list', methods: ['GET'])]
    public function myArchivedList(Request $request): Response
    {
        $user = $this->getUser();

        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $listes = $user->getListeId()->filter(function ($liste) {
            return $liste->isIsArchived();
        });

        return $this->render('liste/myArchivedList.html.twig', [
            'listes' => $listes
        ]);
    }*/

    #[Route('/mes-listes-archivees', name: 'my_archived_list', methods: ['GET'])]
    public function myArchivedList(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $listes = $user->getListeId()->filter(function ($liste) {
            return $liste->isIsArchived();
        });

        return $this->render('liste/mylist.html.twig', [
            'listes' => $listes,
        ]);
    }


}
