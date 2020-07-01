<?php

namespace App\Controller\Admin;

use App\Entity\User;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<a href="/">Rpg Builder</a>');
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'icon class', EntityClass::class);
        return [
            MenuItem::section(),
            MenuItem::linktoRoute('Accueil', 'fa fa-home', 'home'),
            MenuItem::linkToLogout('Déconnexion', 'fas fa-sign-out-alt'),
//            MenuItem::linktoDashboard('Dashboard', 'fas fa-tachometer-alt'),
            MenuItem::section(),
            MenuItem::linkToCrud('Utilisateur', 'fa fa-user', User::class),

        ];
    }
}
