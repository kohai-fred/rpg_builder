<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_register")
     * @Route("/inscription/edit/{id<\d+>}", name="edit_user")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AppAuthenticator $authenticator, EntityManagerInterface $objectManager, User $user = null): Response
    {
        if($user === null){
            $user = new User();
        }

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'validation_groups' => [
                'Default',
                ($user->getId() ? "Modification" : "Inscription")
            ]
        ]);
//        $form->add('submit', SubmitType::class,[
//            'label' => ($user->getId() ? "Editer" : "Ajouter") . " votre profil"
//        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );

            $objectManager->persist($user);
            $objectManager->flush();
            return $this->redirectToRoute('home');
        }
//        $this->addFlash('register_success', 'Bravo inscription réussi !');

        return $this->render('registration/register.html.twig',  [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/change/password", name="change_password")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function changeUserPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $oldPassword = $request->request->get('change_password')['old_password'];
            $isPasswordValid = $passwordEncoder->isPasswordValid($user, $oldPassword);
            if($isPasswordValid == false){
                $this->addFlash('password_error', "Votre ancien mot de passe est incorrect !");

            } else {

//                $newPassword = $form->get('new_password')->getData();
//
//                $user->setPassword($newPassword);
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('new_password')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('new-password', "Votre mot de passe a bien été changé !");

                return $this->redirectToRoute('profile');
            }
        }

        return $this->render('registration/change_password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);

    }

    /**
     * @Route("/oubli-pass", name="app_forgotten_password")
     */
    public function forgottenPaswword(Request $request, UserRepository $userRepo, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        $userCurrent = $this->getUser();

        if($form->isSubmitted() && $form->isValid()){

            $data = $form->getData();
            $user = $userRepo->findOneByEmail($data['email']);

            if(!$user | $user != $userCurrent ){
                $this->addFlash('danger', "Cette adresse n'existe pas !");
                return $this->redirectToRoute('app_forgotten_password');
            }

//            if($user != $userCurrent ){
////                dd($userCurrent, $user);
//                $this->addFlash('danger', "Ce n'est pas votre adresse !");
//                return $this->redirectToRoute('app_forgotten_password');
//            }

            $token = $tokenGenerator->generateToken();


            try{
                $user->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            }catch (\Exception $e){
                $this->addFlash('warning', "Une erreur est survenue : " . $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

            $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL );

            $message = (new \Swift_Message('Mot de passe oublié'))
                ->setFrom('adresse@monsite.fr')
                ->setTo($user->getEmail())
                ->setBody(
                    "<p>Bonjour,</p><p>Une demande de réinitialisation de mot de passe a été effectuée pour le site RPG Builder.fr. Veuiller cliquer sur le lien suivant : </p><a href=' " . $url . "'>Cliquez ici</a> " , 'text/html'
                )
            ;

            $mailer->send($message);

            $this->addFlash('message', "Un email de réinitialisation de mot de passe vous a été envoyé");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/forgotten_password.html.twig', ['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/reset-pass/{token}", name="app_reset_password")
     */
    public function resetPassword($token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token' => $token]);

        if(!$user){

            $this->addFlash('danger', 'Token inconnu');
            return $this->redirectToRoute('app_login');
        }

        if($request->isMethod('POST')){

            $user->setResetToken(null);

            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('message', 'Mot de passe modifié avec succès');

            return $this->redirectToRoute('app_login');
        } else {
            return $this->render('registration/reset_password.html.twig', [
                'token' => $token
            ]);
        }
    }
}
