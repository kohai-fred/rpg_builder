<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
//
            $user = $this->getUser();

        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/{id<\d+>", name="user")
     * @Route("/profile", name="profile")
     */
    public function detail(User $user = null)
    {
        if($user === null){
            $user = $this->getUser();
        }

        if($user === null){
            return $this->redirectToRoute('home');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/user/delete/{id<\d+>}", name="delete_user")
     */
    public function deleteUser(User $user, EntityManagerInterface $objectManager, TokenStorageInterface $tokenStorage)
    {
        $tokenStorage->setToken();
        $objectManager->remove($user);
        $objectManager->flush();
        return $this->redirectToRoute('app_logout');
    }


}
