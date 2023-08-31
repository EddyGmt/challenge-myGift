<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Security\UserAuthentificatorAuthenticator;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Service\MailerService;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request                          $request,
        UserPasswordHasherInterface      $userPasswordHasher,
        UserAuthenticatorInterface       $userAuthenticator,
        UserAuthentificatorAuthenticator $authenticator,
        EntityManagerInterface           $entityManager,
        MailerService                    $mail,
        JWTService                       $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // On génère le JWT de l'utilisateur
            // On crée le Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // On crée le Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // On génère le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // do anything else you need here, like send an email
            if ($user->isIsActive() === false) {
                //Envoie de l'email de validation
                $mail->send(
                    'eddygomet@gmail.com',
                    $user->getEmail(),
                    'Activation de votre compte MyGift',
                    'activation',
                    compact('user', 'token')
                );

            }
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);

    }

    //Activation du compte
    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        //On vérifie si le token est valide, n'a pas expiré et n'a pas été modifié
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            //On vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if ($user && !$user->isIsActive()) {
                $user->setIsActive(true);
                $em->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
            }
        }
        // Ici un problème se pose dans le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

    //Envoie un mail pour reset son mdp
    #[Route('/reset-mdp', name: 'forgotten_password')]
    public function forgottenPassword(
        Request                 $request,
        UserRepository          $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface  $em,
        MailerService           $mail): Response
    {

        $form = $this->createForm(ResetPasswordRequestType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            if ($user) {
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $userRepository->save($user, true);

                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                //Envoie du mail
                $mail->send(
                        'eddygomet@gmail.com',
                        $user,
                        'Réinitialisation de votre mot de passe',
                        'resetmdp',
                        compact('user', 'token')
                );

                $this->addFlash('background-color: #EFF;border: 1px solid #DEE;color: #9AA;', 'Email de réinitialisation envoyé');
                return $this->redirectToRoute('app_login');
            }
            $this->addFlash('background-color: #FDF7DF;border: 1px solid #FEEC6F;color: #C9971C;', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    //Form pour reset son mdp
    #[Route('/reset-mdp/{token}', name: 'reset_mdp')]
    public function resetMdp(
        string                  $token,
        Request                 $request,
        UserRepository          $userRepository,
        EntityManagerInterface  $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $userRepository->findOneByResetToken($token);

        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setResetToken('');

                $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
                $user->setPassword($hashedPassword);

                $userRepository->save($user, true);

                $this->addFlash('background-color: #EFF;border: 1px solid #DEE;color: #9AA;', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }

        $this->addFlash('background-color: #FDF7DF;border: 1px solid #FEEC6F;color: #C9971C;', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }
}
