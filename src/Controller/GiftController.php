<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Entity\Liste;
use App\Form\GiftType;
use App\Form\ReservationType;
use App\Repository\GiftRepository;
use App\Repository\ListeRepository;
use App\Repository\ReservationRepository;
use App\Service\JWTService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/create-gift/{listeId}', name: 'create_gift', methods: ['GET', 'POST'])]
    public function createGift(
        Request                $request,
                               $listeId,
        EntityManagerInterface $entityManager,
        GiftRepository         $giftRepository
    ): Response
    {
        // Chargez la liste à partir de l'ID
        $liste = $entityManager->getRepository(Liste::class)->find($listeId);

        if (!$liste) {
            throw $this->createNotFoundException('Liste non trouvée pour l\'ID ' . $listeId);
        }

        $gift = new Gift();
        $gift->setListe($liste); // Associez le cadeau à la liste
        $form = $this->createForm(GiftType::class, $gift);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftRepository->save($gift, true);

            return $this->redirectToRoute('app_gift_show', [
                'id' => $gift->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        $entityManager->persist($gift);

        return $this->renderForm('gift/create_gift.html.twig', [
            'gift' => $gift,
            'form' => $form,
        ]);
    }

    //TODO effectuer une resa
    #[Route('/reserve-gift/{id}', name: 'reservation', methods: ['POST'])]
    public function reserveGift(
        Request       $request,
        Gift          $gift,
        Liste         $liste,
        MailerService $mail
    ): Response
    {
        // Créez une instance du formulaire de réservation
        $form = $this->createForm(ReservationType::class, $gift);

        //Récupérer le créateur de la liste
        $listeCreator = $liste->getUserId();
        $creatorEmail = $listeCreator->getEmail();

        //On génère un token propre à la reservation du gift
        //$token = bin2hex(random_bytes(16));

        $idGift = $gift->getId(); // ID du cadeau
        $idList = $liste->getId(); // ID de la liste
        $secretSalt = 'votre_sel_secret';
        $token = hash('sha256', $idGift . $idList . $secretSalt);


        // Traitez la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrez les modifications dans la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            $formData = $form->getData();

            $gift->setIsReserved(true);
            $name = $gift->setReservedBy($formData['Name']);
            $email = $gift->setEmailReservation($formData['Email']);
            $giftToken = $gift->setToken($token);

            //Envoie de l'email à celui qui reservé le gift
            $mail->send(
                'eddygomet@gmail.com',
                $email,
                'Réservation prise en compte',
                'reservationUser',
                compact('gift', 'name', 'giftToken')
            );

            //mail à envoyer au créateur de la liste
            $mail->send(
                'eddygomet@gmail.com',
                $creatorEmail,
                'Réservation d\'un de vos gifts',
                'reservationCreator',
                compact('gift')
            );

            // Redirigez l'utilisateur vers une page de confirmation
            return $this->redirectToRoute('confirmation_page');
        }

        // Affichez le formulaire
        return $this->render('gift/reserve.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //TODO se désabo d'une resa
    #[Route('/cancel-reservation/{token}', name: 'cancel_reservation', methods: ['POST'])]
    public function cancelReservation
    (
        Request                $request,
        EntityManagerInterface $em,
                               $token,
        Gift                   $gift
    ): Response
    {
        // Recherchez le cadeau dans la base de données en utilisant le token
        $entityManager = $this->getDoctrine()->getManager();
        $gift = $entityManager->getRepository(Gift::class)->findOneBy(['token' => $token]);

        if (!$gift) {
            // Le cadeau n'a pas été trouvé, redirigez vers une page d'erreur ou affichez un message approprié
            throw $this->createNotFoundException('Cadeau non trouvé');
        }

        if ($gift) {
            $giftToken = $gift->getToken(); // Supposons que la méthode pour obtenir le token est getToken() dans votre classe Gift

            if ($token === $giftToken) {
                // Les tokens correspondent, vous pouvez effectuer les opérations nécessaires
                $name = $gift->setReservedBy(null);
                $email = $gift->setEmailReservation(null);
                $email = $gift->setToken(null);
            } else {
                return dd('No matching Token');
            }
        } else {
            return dd('Cadeau introuvable');
        }

        return $this->redirectToRoute('confirmation_page');
    }


    //TODO méthode pour scrapper un gift
//    public function getScrapedGift(
//        Request                $request,
//        EntityManagerInterface $entityManager,
//        GiftRepository         $giftRepository
//    ): Response
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
//        $gift = new Gift();
//        $gift->setImage($image);
//        $gift->setName($name);
//        $gift->setPrice($price);
//        $gift->setImage($image);
//
//        return $this->renderForm('gift/create_gift.html.twig', [
//            'gift' => $gift,
//            'form' => $form,
//        ]);
//    }

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
        if ($this->isCsrfTokenValid('delete' . $gift->getId(), $request->request->get('_token'))) {
            $giftRepository->remove($gift, true);
        }

        return $this->redirectToRoute('app_gift_index', [], Response::HTTP_SEE_OTHER);
    }

    //    #[Route('/new', name: 'app_gift_new', methods: ['GET', 'POST'])]
//    public function new(Request $request,
//                        GiftRepository $giftRepository,
//                        ListeRepository $listeRepository,
//                        $listeId): Response
//    {
//
//        $user = $this->getUser();
////        $listeId = $request->query->get('listeId');
//        $liste = $listeRepository->find($listeId);
//        $liste = $this->getDoctrine()->getRepository(Liste::class)->find($listeId);
//
//
//        if (!$user) {
//            return $this->redirectToRoute('app_login');
//        }
//
//        if (!$liste) {
//            // Gérer le cas où la liste n'est pas trouvée
//            // Par exemple, rediriger vers une page d'erreur
//            return $this->redirectToRoute('app_liste_new');
//        }
//
//
//        if (!$user->isIsActive()) {
//            return $this->render('resend_verif');
//        }else{
//            $gift = new Gift();
//            $gift->setListe($liste); // Associez le cadeau à la liste
//            $form = $this->createForm(GiftType::class, $gift);
//            $form->handleRequest($request);
////            $gift->setListe($this->getlist);
//
//            if ($form->isSubmitted() && $form->isValid()) {
//                $giftRepository->save($gift, true);
//
//
//                return $this->redirectToRoute('app_gift_show', [
//                    'gift'=>$gift,
//                ], Response::HTTP_SEE_OTHER);
//            }
//
//            return $this->renderForm('gift/new.html.twig', [
//                'gift' => $gift,
//                'form' => $form,
//            ]);
//        }
//    }

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
