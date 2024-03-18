<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Entity\Liste;
use App\Form\GiftType;
use App\Repository\GiftRepository;
use App\Repository\ListeRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $gift->setListe($liste);
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

    #[Route('reserve-gift/{id}', name: 'reservation_gift', methods: 'POST')]
    public function reserveGift(
        Request         $request,
        Gift            $gift,
        ListeRepository $listeRepository,
        MailerService   $mail,
        ManagerRegistry $doctrine
    ): Response
    {
        $liste = $gift->getListe();

        // Vérifier si la liste est archivée
        if ($liste->isIsArchived()) {
            // Si la liste est archivée, vous pouvez rediriger l'utilisateur vers une page d'erreur ou afficher un message approprié.
            return $this->render('liste/liste_archive.html.twig');
        }

        // Récupérer les données du formulaire directement depuis la requête
        $name = $request->request->get('Name');
        $email = $request->request->get('Email');

        // Générer le token
        $idGift = $gift->getId();
        $secretSalt = 'votre_sel_secret';
        $token = hash('sha256', $idGift . $secretSalt);

        // Mettre à jour l'état du cadeau et les autres champs
//        $entityManager = $this->getDoctrine()->getManager();
        $entityManager = $doctrine->getManager();
        $gift->setIsReserved(true);
        $gift->setReservedBy($name);
        $gift->setEmailReservation($email);
        $gift->setToken($token);
        $entityManager->flush();

        // Envoie de l'e-mail à l'utilisateur qui a réservé le cadeau
        $mail->send(
            'eddygomet@gmail.com',
            $email,
            'Réservation prise en compte',
            'reservationUser',
            compact('gift', 'name', 'token')
        );

        // Redirection vers une page de confirmation ou une autre page appropriée
        return $this->redirectToRoute('app_gift_show', ['id' => $gift->getId()]);

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
    #[Route('/scrapped-gift', name:'app_scrapped', methods: ['GET', 'POST'])]
    public function scrapGift(
        Request                $request,
        EntityManagerInterface $entityManager,
        GiftRepository         $giftRepository
    ): Response
    {
        //On récupère le lien qui sera notre requete
        $link ='https://www.amazon.fr/dp/B0BWRHQZZN/ref=sspa_dk_detail_1?psc=1&pd_rd_i=B0BWRHQZZN&pd_rd_w=NCTBI&content-id=amzn1.sym.2b631440-6276-45ab-a2e4-50e8867cfe1d&pf_rd_p=2b631440-6276-45ab-a2e4-50e8867cfe1d&pf_rd_r=WBMYPF0WS7451796Q771&pd_rd_wg=e2ZvD&pd_rd_r=4316d44f-2812-40bc-a76b-ae44a5dfb578&s=videogames&sp_csd=d2lkZ2V0TmFtZT1zcF9kZXRhaWwy';
        $client = new Client();
        $crawler = $client->request('GET', $link);

        //On filtre et récupère les infos à partir du crawler pour les mettre là où on veut
        $name = $crawler->filter('#productTitle')->text();
        $image = $crawler->filter('#landingImage')->text();
        $price = $crawler->filter('#corePriceDisplay_desktop_feature_div > div.a-section.a-spacing-none.aok-align-center.aok-relative > span.a-price.aok-align-center.reinventPricePriceToPayMargin.priceToPay > span:nth-child(2)')->text();

        $cleanedPriceString = preg_replace("/[^0-9,.]/", "", $price);
        $cleanedPriceString = str_replace(",", ".", $cleanedPriceString);
        $cleanedPriceString = str_replace(" ", "", $cleanedPriceString);
        $priceFloat = floatval($cleanedPriceString);

        $gift = new Gift();
        $gift->setImageName($image);
        $gift->setName($name);
        $gift->setPrice($priceFloat);

/*        return $this->renderForm('gift/create_gift.html.twig', [
            'gift' => $gift,
            'form' => $form,
        ]);*/
        return dd($name, $image, $priceFloat);
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
