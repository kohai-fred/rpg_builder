<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
        $form->add('submit', SubmitType::class, [
            'label'=> 'Modifier',
            'attr' => [
                'class'=>'btn btn-green'
            ]
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
//            dd($form);

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
}
