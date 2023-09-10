<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Form\GiftType;
use App\Repository\GiftRepository;
use App\Repository\ListeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Goutte\Client;

#[Route('/gift')]
class GiftController extends AbstractController
{
    #[Route('/', name: 'app_gift_index', methods: ['GET'])]
    public function index(GiftRepository $giftRepository): Response
    {
        return $this->render('gift/index.html.twig', [
            'gifts' => $giftRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_gift_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        GiftRepository $giftRepository,
                        ListeRepository $listeRepository): Response
    {

        $user = $this->getUser();
        $lisetId = $request->query->get('listeId');
        $liste = $listeRepository->find($lisetId);


        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$liste) {
            // Gérer le cas où la liste n'est pas trouvée
            // Par exemple, rediriger vers une page d'erreur
            return $this->redirectToRoute('app_liste_new');
        }


        if (!$user->isIsActive()) {
            return $this->render('resend_verif');
        }else{
            $gift = new Gift();
            $gift->setListe($liste); // Associez le cadeau à la liste
            $form = $this->createForm(GiftType::class, $gift);
            $form->handleRequest($request);
//            $gift->setListe($this->getlist);

            if ($form->isSubmitted() && $form->isValid()) {
                $giftRepository->save($gift, true);


                return $this->redirectToRoute('app_gift_show', [
                    'gift'=>$gift,
                ], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('gift/new.html.twig', [
                'gift' => $gift,
                'form' => $form,
            ]);
        }
    }

    #[Route('/{id}', name: 'app_gift_show', methods: ['GET'])]
    public function show(Gift $gift): Response
    {
        return $this->render('gift/show.html.twig', [
            'gift' => $gift,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gift_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Gift $gift, GiftRepository $giftRepository): Response
    {
        $form = $this->createForm(GiftType::class, $gift);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftRepository->save($gift, true);

            return $this->redirectToRoute('app_gift_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gift/edit.html.twig', [
            'gift' => $gift,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gift_delete', methods: ['POST'])]
    public function delete(Request $request, Gift $gift, GiftRepository $giftRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gift->getId(), $request->request->get('_token'))) {
            $giftRepository->remove($gift, true);
        }

        return $this->redirectToRoute('app_gift_index', [], Response::HTTP_SEE_OTHER);
    }

//    public function scrapGift(Request $request, Gift $gift, GiftRepository $giftRepository): Response
//    {
//        //On récupère le lien qui sera notre requete
//        $link = $request->request->get('link'); // Récupère le lien depuis le formulaire
//        $client = new Client();
//        $crawler = $client->request('GET', $link);
//
//        //On filtre et récupère les infos à partir du crawler pour les mettre là où on veut
//        $name = $crawler->filter('name')->text();
//        $description = $crawler->filter('description')->text();
//        $image = $crawler->filter('img')->text();
//        $price = $crawler->filter('price')->text();
//
//
//        $gift = new Gift();
//        $gift->setImage($image);
//        $gift->setName($name);
//        $gift->setPrice($price);
//        $gift->setImage($image);
//
//
//        return;
//    }
}
